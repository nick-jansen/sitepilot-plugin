<?php

use Sitepilot\Plugin\Cache\CacheService;
use Sitepilot\Plugin\Branding\BrandingService;

if (!function_exists('sitepilot_cache')) {
    function sitepilot_cache(): CacheService
    {
        return sitepilot()->get('cache');
    }
}

if (!function_exists('sitepilot_branding')) {
    function sitepilot_branding(): BrandingService
    {
        return sitepilot()->get('branding');
    }
}
