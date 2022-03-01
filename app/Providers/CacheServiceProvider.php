<?php

namespace Sitepilot\Plugin\Providers;

use Sitepilot\Plugin\Services\CacheService;
use Sitepilot\Plugin\Services\BrandingService;
use Sitepilot\Framework\Support\ServiceProvider;

class CacheServiceProvider extends ServiceProvider
{
    private CacheService $cache;

    /**
     * Bootstrap application services and hooks.
     */
    public function boot(CacheService $cache): void
    {
        if (
            !$cache->is_page_cache_enabled()
            && !$cache->is_object_cache_enabled()
            && !current_user_can($cache->capability())
        ) {
            return;
        }

        $this->cache = $cache;

        $this->add_action('admin_init', '@handle_manual_purge');
        $this->add_action('transition_post_status', 'purge_post_on_update', 10, 3);
        $this->add_action('delete_post', 'purge_post_on_delete', 10, 1);
        $this->add_action('switch_theme', 'purge_page_cache');
        $this->add_action('comment_post', 'purge_post_on_comment', 10, 2);
        $this->add_action('wp_set_comment_status', 'purge_post_by_comment');
        $this->add_action('sitepilot_purge_object_cache', 'purge_object_cache');
        $this->add_action('sitepilot_purge_page_cache', 'purge_page_cache');

        $this->call('register_purge_menu');
    }

    /**
     * Add purge menu to the admin bar.
     */
    public function register_purge_menu(): void
    {
        $this->app->add_admin_bar_node([
            'id' => 'sp-cache',
            'title' => __('Cache', 'sitepilot'),
            'parent' => 'top-secondary'
        ]);

        if ($this->cache->is_page_cache_enabled() && $this->cache->is_object_cache_enabled()) {
            $this->app->add_admin_bar_node([
                'id' => 'sp-cache-purge-all',
                'parent' => 'sp-cache',
                'title' => __('Purge All Caches', 'sitepilot'),
                'href' => wp_nonce_url(add_query_arg('sp-cache', 'purge-all', admin_url()), 'purge-all'),
            ]);
        }

        if ($this->cache->is_page_cache_enabled()) {
            $this->app->add_admin_bar_node([
                'id' => 'sp-cache-purge-page',
                'parent' => 'sp-cache',
                'title' => __('Purge Page Cache', 'sitepilot'),
                'href' => wp_nonce_url(add_query_arg('sp-cache', 'purge-page', admin_url()), 'purge-page'),
            ]);
        }

        if ($this->cache->is_object_cache_enabled()) {
            $this->app->add_admin_bar_node([
                'id' => 'sp-cache-purge-object',
                'parent' => 'sp-cache',
                'title' => __('Purge Object Cache', 'sitepilot'),
                'href' => wp_nonce_url(add_query_arg('sp-cache', 'purge-object', admin_url()), 'purge-object'),
            ]);
        }
    }

    /**
     * Handle manual cache purge.
     */
    public function handle_manual_purge(
        BrandingService $branding
    ): void {
        if (isset($_GET['purge_success'])) {
            if ($_GET['purge_success']) {
                $this->app->add_admin_notice(sprintf(__('%s cache successfully cleared!', 'sitepilot'), $branding->name()));
            } else {
                $this->app->add_admin_notice(sprintf(__('Failed to clear %s cache, please contact support!', 'sitepilot'), $branding->name()), 'error');
            }
        }

        if (!current_user_can($this->cache->capability())) {
            return;
        }

        $action = filter_input(INPUT_GET, 'sp-cache');

        if (!$action || !in_array($action, array('purge-all', 'purge-object', 'purge-page'))) {
            return;
        }

        if (!wp_verify_nonce(filter_input(INPUT_GET, '_wpnonce'), $action)) {
            return;
        }

        if ('purge-all' === $action) {
            $purge = $this->cache->purge_object_cache() && $this->cache->purge_page_cache();
            $type  = 'all';
        }

        if ('purge-object' === $action) {
            $purge = $this->cache->purge_object_cache();
            $type  = 'object';
        }

        if ('purge-page' === $action) {
            $purge = $this->cache->purge_page_cache();
            $type  = 'page';
        }

        $redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : admin_url();

        wp_safe_redirect(add_query_arg(array(
            'purge_success' => (int) $purge,
            'cache_type'    => $type,
        ), $redirect_url));
    }

    /**
     * When a post is transitioned to 'publish' for the first time purge the
     * entire site cache. This ensures blog pages, category archives, author archives,
     * search results and the 'Latest Posts' footer section is accurate. Otherwise,
     * only update the current post URL.
     * 
     * @param string $new_status
     * @param string $old_status
     * @param \WP_Post $post
     */
    public function purge_post_on_update($new_status, $old_status, $post): bool
    {
        $post_type = get_post_type($post);

        if (!in_array($post_type, $this->cache->public_post_types())) {
            return false;
        }

        if (in_array($post_type, $this->cache->types_excluded_from_purge())) {
            return false;
        }

        if (!$this->should_purge_post_status($new_status, $old_status)) {
            return false;
        }

        if ($post_type === 'customize_changeset' && $new_status === 'trash') {
            return false;
        }

        if (in_array($post_type, $this->cache->types_needing_single_purge())) {
            return $this->cache->purge_post($post);
        }

        return $this->cache->purge_page_cache();
    }

    /**
     * Purge the entire cache when a post type is deleted.
     * 
     * @param int|\WP_Post $post_id
     */
    public function purge_post_on_delete($post): bool
    {
        $post_type = get_post_type($post);

        if (!in_array($post_type, $this->cache->public_post_types())) {
            return false;
        }

        if (in_array($post_type, $this->cache->types_excluded_from_purge())) {
            return false;
        }

        $post_status = get_post_status($post);

        if (in_array($post_status, array('auto-draft', 'draft', 'trash'))) {
            return false;
        }

        return $this->cache->purge_page_cache();
    }

    /**
     * Purge a post on new comment (if approved).
     *
     * @param int $comment_id
     * @param bool $comment_approved
     */
    public function purge_post_on_comment($comment_id, $comment_approved): bool
    {
        if (!$comment_approved) {
            return false;
        }

        return $this->cache->purge_post_by_comment($comment_id);
    }

    /**
     * Should a post be purged based on the new/old status.
     *
     * @param string $new_status
     * @param string $old_status
     */
    private function should_purge_post_status($new_status, $old_status): bool
    {
        // A newly created post with no content
        if ($new_status === 'auto-draft') {
            return false;
        }

        // A post in draft status
        if ($new_status === 'draft' && in_array($old_status, array('auto-draft', 'draft', 'trash'))) {
            return false;
        }

        // A post in trash status
        if ($new_status === 'trash' && in_array($old_status, array('auto-draft', 'draft', 'trash'))) {
            return false;
        }

        return $this->cache->should_purge_post_status();
    }
}
