<?php

namespace Sitepilot\Plugin\Providers;

use Sitepilot\Plugin\Services\BrandingService;
use Sitepilot\Framework\Support\ServiceProvider;
use Sitepilot\Plugin\Services\LitespeedCacheService;

class LitespeedCacheServiceProvider extends ServiceProvider
{
    private LitespeedCacheService $cache;

    /**
     * Bootstrap application services and hooks.
     */
    public function boot(LitespeedCacheService $cache): void
    {
        if (!$cache->is_enabled()) {
            return;
        }

        $this->cache = $cache;

        $this->add_action('init', 'send_headers', 99);
        $this->add_action('init', '@handle_manual_purge');
        $this->add_action('transition_post_status', 'purge_post_on_update', 10, 3);
        $this->add_action('delete_post', 'purge_post_on_delete', 10, 1);
        $this->add_action('switch_theme', 'purge_page_cache');
        $this->add_action('comment_post', 'purge_post_on_comment', 10, 2);
        $this->add_action('wp_set_comment_status', 'purge_post_on_comment', 10, 2);

        $this->call('register_purge_menu');
    }

    /**
     * Send cache headers.
     */
    public function send_headers(): void
    {
        if ($this->cache->is_page_cacheable()) {
            header('X-LiteSpeed-Cache-Control: public,max-age=' . $this->cache->max_age());
        }

        if (!headers_sent() && $this->cache->purge_queue()) {
            header('X-LiteSpeed-Purge: ' . implode(',', $this->cache->purge_queue()));
            $this->cache->reset_purge_queue();
        }
    }

    /**
     * Add purge menu to the admin bar.
     */
    public function register_purge_menu(): void
    {
        if (current_user_can($this->cache->capability())) {
            $this->app->add_admin_bar_node([
                'id' => 'sp-cache',
                'title' => __('Clear Cache', 'sitepilot'),
                'parent' => 'top-secondary',
                'href' => wp_nonce_url(add_query_arg('sp-cache', 'purge-page', admin_url()), 'purge-page'),
            ]);
        }
    }

    /**
     * Handle manual cache purge.
     */
    public function handle_manual_purge(
        BrandingService $branding
    ): void {
        if (!current_user_can($this->cache->capability())) {
            return;
        }

        if (isset($_GET['purge_success'])) {
            if ($_GET['purge_success']) {
                $this->app->add_admin_notice(sprintf(__('%s cache successfully cleared!', 'sitepilot'), $branding->name()));
            } else {
                $this->app->add_admin_notice(sprintf(__('Failed to clear %s cache, please contact support!', 'sitepilot'), $branding->name()), 'error');
            }
        }

        $action = filter_input(INPUT_GET, 'sp-cache');

        if (!$action || !in_array($action, array('purge-page'))) {
            return;
        }

        if (!wp_verify_nonce(filter_input(INPUT_GET, '_wpnonce'), $action)) {
            return;
        }

        $this->cache->purge('*');

        wp_safe_redirect(add_query_arg(array(
            'purge_success' => true,
            'cache_type'    => 'page',
        ), isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : admin_url()));
    }

    /**
     * Purge page cache.
     */
    public function purge_page_cache(): bool
    {
        return $this->cache->purge('*');
    }

    /**
     * Purge cache when a post is updated.
     */
    public function purge_post_on_update($new_status, $old_status, $post): bool
    {
        $post_type = get_post_type($post);

        // Only purge public post types
        if (!in_array($post_type, $this->cache->public_post_types())) {
            return false;
        }

        // Exclude post types from purge
        if (in_array($post_type, $this->cache->post_types_excluded_from_purge())) {
            return false;
        }

        if ($post_type === 'customize_changeset' && $new_status === 'trash') {
            return false;
        }

        // Post types which need a single purge
        if (in_array($post_type, $this->cache->post_types_needing_single_purge())) {
            return $this->cache->purge_post($post);
        }

        return $this->cache->purge('*');
    }

    /**
     * Purge the entire cache when a post type is deleted.
     */
    public function purge_post_on_delete($post): bool
    {
        $post_type = get_post_type($post);

        if (!in_array($post_type, $this->cache->public_post_types())) {
            return false;
        }

        if (in_array($post_type, $this->cache->post_types_excluded_from_purge())) {
            return false;
        }

        $post_status = get_post_status($post);

        if (in_array($post_status, array('auto-draft', 'draft', 'trash'))) {
            return false;
        }

        return $this->cache->purge('*');
    }

    /**
     * Purge a post on new comment (if approved).
     *
     * @param int $comment_id
     * @param bool $comment_approved
     */
    public function purge_post_on_comment($comment_id, $comment_approved = true): bool
    {
        if (!$comment_approved) {
            return false;
        }

        return $this->cache->purge_comment($comment_id);
    }
}
