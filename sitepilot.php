<?php

use Sitepilot\Framework\Foundation\Application;

/**
 * Plugin Name: Sitepilot
 * Plugin URI: https://sitepilot.io
 * Author: Sitepilot
 * Author URI: https://sitepilot.io
 * Version: 1.0.0-dev
 * Description: Brings the powers of Sitepilot directly to your WordPress website.
 * Text Domain: sitepilot
 */

if (!defined('ABSPATH')) exit;

require __DIR__ . '/vendor/autoload.php';

new Application('sitepilot', __FILE__, [
    \Sitepilot\Plugin\Providers\PluginServiceProvider::class,
    \Sitepilot\Plugin\Providers\CacheServiceProvider::class,
    \Sitepilot\Plugin\Providers\UpdateServiceProvider::class,
    \Sitepilot\Plugin\Providers\BrandingServiceProvider::class,
    \Sitepilot\Plugin\Providers\DashboardServiceProvider::class,
    \Sitepilot\Plugin\Providers\ClientRoleServiceProvider::class
]);

if (!function_exists('sitepilot')) {
    function sitepilot(): ?Application
    {
        return Application::app('sitepilot');
    }
}
