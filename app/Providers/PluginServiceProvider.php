<?php

namespace Sitepilot\Plugin\Providers;

use Sitepilot\Framework\Support\ServiceProvider;

class PluginServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap application services and hooks.
     */
    public function boot(): void
    {
        $this->add_action('admin_enqueue_scripts', 'enqueue_admin_assets');
    }

    /**
     * Enqueue admin scripts and styles.
     */
    function enqueue_admin_assets(): void
    {
        $handle = $this->namespace('', '-');

        wp_enqueue_style($handle, $this->app->public_url('css/admin.css'), [], $this->app->script_version());
    }
}
