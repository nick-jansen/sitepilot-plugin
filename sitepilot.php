<?php

use Sitepilot\Framework\Foundation\Application;
use Sitepilot\Plugin\Providers\BrandingServiceProvider;
use Sitepilot\Plugin\Providers\CacheServiceProvider;
use Sitepilot\Plugin\Providers\ClientRoleServiceProvider;
use Sitepilot\Plugin\Providers\DashboardServiceProvider;
use Sitepilot\Plugin\Providers\EdgeCacheServiceProvider;
use Sitepilot\Plugin\Providers\LitespeedCacheServiceProvider;
use Sitepilot\Plugin\Providers\PluginServiceProvider;
use Sitepilot\Plugin\Providers\UpdateServiceProvider;

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
    PluginServiceProvider::class,
    CacheServiceProvider::class,
    UpdateServiceProvider::class,
    BrandingServiceProvider::class,
    DashboardServiceProvider::class,
    EdgeCacheServiceProvider::class,
    ClientRoleServiceProvider::class,
    LitespeedCacheServiceProvider::class
]);

if (!function_exists('sitepilot')) {
    function sitepilot(): ?Application
    {
        return Application::app('sitepilot');
    }
}
