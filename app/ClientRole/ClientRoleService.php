<?php

namespace Sitepilot\Plugin\ClientRole;

use Sitepilot\Framework\Foundation\Application;

class ClientRoleService
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
    public function enabled(): bool
    {
        return $this->app->filter('client_role/enabled', false);
    }

    /**
     * Get the excluded capabilities list.
     */
    public function excluded_capabilities(): array
    {
        return $this->app->filter('client_role/excluded_capabilities', [
            'switch_themes',
            'edit_themes',
            'activate_plugins',
            'edit_plugins',
            'edit_users',
            'edit_files',
            'delete_users',
            'create_users',
            'update_plugins',
            'delete_plugins',
            'install_plugins',
            'update_themes',
            'install_themes',
            'update_core',
            'remove_users',
            'promote_users',
            'delete_themes'
        ]);
    }
}
