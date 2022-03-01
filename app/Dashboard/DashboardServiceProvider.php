<?php

namespace Sitepilot\Plugin\Dashboard;

use Sitepilot\Plugin\Cache\CacheService;
use Sitepilot\Plugin\Branding\BrandingService;
use Sitepilot\Framework\Support\ServiceProvider;

class DashboardServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap application services and hooks.
     */
    public function boot(DashboardService $dashboard): void
    {
        $this->add_action('admin_menu', '@register_admin_menu');

        if ($dashboard->support_enabled()) {
            $this->add_action('in_admin_footer', '@admin_support_script');
        }
    }

    /**
     * Register Sitepilot menu to the dashboard.
     */
    public function register_admin_menu(BrandingService $branding): void
    {
        $slug = $this->app->namespace('menu', '-');

        add_menu_page(
            $branding->name(),
            $branding->name(),
            'publish_posts',
            $slug,
            '',
            false,
            2
        );

        $page_hook_suffix = add_submenu_page(
            $slug,
            $branding->name(),
            __('Dashboard', 'sitepilot'),
            'publish_posts',
            $slug,
            [$this, 'render'],
            -99
        );

        $this->add_action("admin_print_scripts-{$page_hook_suffix}", '@enqueue_assets');
    }

    /**
     * Enqueue dashboard assets.
     */
    function enqueue_assets(
        CacheService $cache,
        BrandingService $branding,
        DashboardService $dashboard
    ): void {
        $id = $this->app->namespace('dashboard', '-');

        wp_enqueue_style(
            $id,
            $this->app->public_url('css/dashboard.css'),
            ['wp-components'],
            $this->app->script_version()
        );

        wp_enqueue_script(
            $id,
            $this->app->public_url('js/dashboard.js'),
            ['wp-api', 'wp-i18n', 'wp-components', 'wp-element'],
            $this->app->script_version(),
            true
        );

        global $wp_version;

        wp_localize_script(
            $id,
            'sitepilot',
            array(
                'version' => $this->app->version(),
                'plugin_url' => $this->app->url(),
                'branding_name' => $branding->name(),
                'support_email' => $branding->support_email(),
                'support_url' => $branding->support_website(),
                'server_name' => gethostname(),
                'php_version' => phpversion(),
                'wp_version' => $wp_version,
                'powered_by' => $branding->powered_by(),
                'support_enabled' => $dashboard->support_enabled(),
                'cache_status' => $cache->is_page_cache_enabled() ? __('On', 'sitepilot') : __('Off', 'sitepilot')
            )
        );
    }

    /**
     * Render the dashboard.
     */
    public function render(): void
    {
        echo '<div class="sp-dashboard sitepilot" id="sitepilot-dashboard"></div>';
    }

    /**
     * Enqueue support widget.
     */
    public function admin_support_script(BrandingService $branding): void
    {
        $screen = get_current_screen();

        if (!empty($screen->id) && in_array($screen->id, ['dashboard', 'toplevel_page_sitepilot-menu'])) {
            echo $branding->support_widget();
        }
    }
}
