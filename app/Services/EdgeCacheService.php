<?php

namespace Sitepilot\Plugin\Services;

use Sitepilot\Framework\Foundation\Application;

class EdgeCacheService
{
    private Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function is_enabled(): bool
    {
        return (bool)getenv('HTTP_SP_PLATFORM');
    }

    public function purge(): bool
    {
        static $purged = false;

        if (!$purged) {
            header('SP-Cache-Control: purge-all');
            $purged = true;
        }

        return $purged;
    }

    public function capability(): string
    {
        return $this->app->filter('cache/capability', 'manage_options');
    }
}
