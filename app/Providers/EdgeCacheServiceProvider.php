<?php

namespace Sitepilot\Plugin\Providers;

use Sitepilot\Framework\Support\ServiceProvider;
use Sitepilot\Plugin\Services\BrandingService;
use Sitepilot\Plugin\Services\EdgeCacheService;

class EdgeCacheServiceProvider extends ServiceProvider
{
    private EdgeCacheService $cache;

    private BrandingService $branding;

    public function boot(EdgeCacheService $cache, BrandingService $branding): void
    {
        $this->cache = $cache;
        $this->branding = $branding;

        if ($cache->is_enabled()) {
            add_action('init', [$this, 'send_cache_headers']);
            add_action('admin_init', [$this, 'handle_manual_purge']);

            // Post ID is received
            add_action('wp_trash_post', [$cache, 'purge'], 0);
            add_action('publish_post', [$cache, 'purge'], 0);
            add_action('edit_post', [$cache, 'purge'], 0);
            add_action('delete_post', [$cache, 'purge'], 0);
            add_action('publish_phone', [$cache, 'purge'], 0);

            // Coment ID is received
            add_action('trackback_post', [$cache, 'purge'], 99);
            add_action('pingback_post', [$cache, 'purge'], 99);
            add_action('comment_post', [$cache, 'purge'], 99);
            add_action('edit_comment', [$cache, 'purge'], 99);
            add_action('wp_set_comment_status', [$cache, 'purge'], 99);

            // No post_id is available
            add_action('switch_theme', [$cache, 'purge'], 99);
            add_action('edit_user_profile_update', [$cache, 'purge'], 99);
            add_action('wp_update_nav_menu', [$cache, 'purge']);
            add_action('clean_post_cache', [$cache, 'purge']);
            add_action('transition_post_status', [$this, 'transition_post_status'], 10, 2);

            if (current_user_can($cache->capability())) {
                $this->call('register_purge_menu');
            }
        }
    }

    public function send_cache_headers(): void
    {
        if (!is_user_logged_in() && !in_array('SP-Cache-Control', headers_list())) {
            header('SP-Cache-Control: public, max-age=315360000');
        } else {
            header('SP-Cache-Control: private');
        }
    }

    public function transition_post_status($new_status, $old_status)
    {
        if ($new_status != $old_status) {
            $this->cache->purge();
        }
    }

    public function register_purge_menu(): void
    {
        $this->app->add_admin_bar_node([
            'id' => 'sp-cache',
            'title' => __('Cache', 'sitepilot'),
            'parent' => 'top-secondary'
        ]);

        $this->app->add_admin_bar_node([
            'id' => 'sp-cache-purge-page',
            'parent' => 'sp-cache',
            'title' => __('Purge Cache', 'sitepilot'),
            'href' => wp_nonce_url(add_query_arg('sp-cache', 'purge-page', admin_url()), 'purge-page'),
        ]);
    }

    public function handle_manual_purge(): void
    {
        if (isset($_GET['purge_success'])) {
            if ($_GET['purge_success']) {
                $this->app->add_admin_notice(sprintf(__('%s cache successfully cleared!', 'sitepilot'), $this->branding->name()));
            } else {
                $this->app->add_admin_notice(sprintf(__('Failed to clear %s cache, please contact support!', 'sitepilot'), $this->branding->name()), 'error');
            }

            return;
        } else if (!current_user_can($this->cache->capability())) {
            return;
        }

        $action = filter_input(INPUT_GET, 'sp-cache');

        if (!$action || $action != 'purge-page') {
            return;
        }

        if (!wp_verify_nonce(filter_input(INPUT_GET, '_wpnonce'), $action)) {
            return;
        }

        if ('purge-page' === $action) {
            $purge = $this->cache->purge();
            $type = 'page';
        }

        $redirect_url = $_SERVER['HTTP_REFERER'] ?? admin_url();

        wp_safe_redirect(add_query_arg(array(
            'purge_success' => $purge ?? false,
            'cache_type' => $type ?? false,
        ), $redirect_url));
    }
}
