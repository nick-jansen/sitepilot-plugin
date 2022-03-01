<?php

use Sitepilot\Plugin\Services\CacheService;
use Sitepilot\Plugin\Services\BrandingService;

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
