<?php

namespace Sitepilot\Plugin\Providers;

use Sitepilot\Plugin\Services\BrandingService;
use Sitepilot\Framework\Support\ServiceProvider;

class BrandingServiceProvider extends ServiceProvider
{
    /**
     * The branding service instance.
     */
    protected BrandingService $branding;

    /**
     * Register application services and filters.
     */
    public function register(): void
    {
        $this->app->alias(BrandingService::class, 'branding');
    }

    /**
     * Bootstrap application services and hooks.
     */
    public function boot(BrandingService $branding): void
    {
        $this->branding = $branding;

        if ($branding->enabled()) {
            $this->add_action('login_enqueue_scripts', 'enqueue_login_style');
            $this->add_filter_value('admin_footer_text', "❤ Powered by {$branding->powered_by()}");
            $this->add_filter_value('login_headerurl', $branding->website());
        }

        $this->add_filter('update_footer', 'filter_admin_footer_version', 11);
        $this->add_shortcode('copyright', 'copyright_shortcode');
    }

    /**
     * Replace WordPress login logo.
     */
    public function enqueue_login_style(): void
    {
?>
        <style>
            .login h1 a {
                background-image: url(<?= $this->branding->logo() ?>) !important;
                background-size: 100% !important;
                background-position: center top !important;
                background-repeat: no-repeat !important;
                height: 70px !important;
                width: 220px !important;
                margin-top: 10px !important;
            }
        </style>
<?php
    }

    /**
     * Add plugin version to admin footer.
     */
    public function filter_admin_footer_version(): string
    {
        global $wp_version;

        $plugin_version = $this->app->version();

        return "<div style=\"text-align: right;\">WordPress v{$wp_version} &sdot; <a href=\"{$this->branding->website()}\" target=\"_blank\">{$this->branding->name()}</a> v{$plugin_version}</div>";
    }

    /**
     * Copyright shortcode.
     */
    public function copyright_shortcode($atts): string
    {
        $atts = shortcode_atts([
            'separator' => '&middot;',
            'text' => 'Powered by'
        ], $atts);

        return sprintf('&copy; %s %s %s %s <a href="%s" target="_blank">%s</a>', get_bloginfo('name'), date('Y'), $atts['separator'], $atts['text'], $this->branding->website(), $this->branding->powered_by());
    }
}
