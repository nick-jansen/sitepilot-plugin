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
        wp_enqueue_style($this->app->namespace(), $this->app->public_url('css/admin.css'), [], $this->app->script_version());
    }
}
