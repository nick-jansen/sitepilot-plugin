<?php

namespace Sitepilot\Plugin\Update;

use Sitepilot\Framework\Foundation\Application;

class UpdateService
{
    /**
     * The application instance.
     */
    private Application $app;

    /**
     * Create a new update service instance.
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Get the update repo url.
     * 
     * @return mixed 
     */
    public function repo(string $slug)
    {
        return sprintf('https://wpupdate.sitepilot.cloud/v1?action=get_metadata&slug=%s', $slug);
    }

    /**
     * Get a list of updatable apps.
     * 
     * @return Application[]
     */
    public function apps()
    {
        return $this->app->filter('update/apps', Application::apps());
    }
}
