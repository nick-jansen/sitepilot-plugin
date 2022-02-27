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
    \Sitepilot\Plugin\PluginServiceProvider::class,
    \Sitepilot\Plugin\Cache\CacheServiceProvider::class,
    \Sitepilot\Plugin\Update\UpdateServiceProvider::class,
    \Sitepilot\Plugin\Branding\BrandingServiceProvider::class,
    \Sitepilot\Plugin\Dashboard\DashboardServiceProvider::class,
    \Sitepilot\Plugin\ClientRole\ClientRoleServiceProvider::class    
]);

if (!function_exists('sitepilot')) {
    function sitepilot(): ?Application
    {
        return Application::app('sitepilot');
    }
}
