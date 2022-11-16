<?php

namespace Sitepilot\Plugin\Services;

use Sitepilot\Framework\Foundation\Application;

class DashboardService
{
    /**
     * The application instance.
     */
    private Application $app;

    /**
     * Create a new client role service instance.
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Determine if the client role is enabled.
     */
    public function support_enabled(): bool
    {
        return $this->app->filter('dashboard/support_enabled', getenv('HTTP_SP_PLATFORM') == 'sitepilot.io');
    }
}
