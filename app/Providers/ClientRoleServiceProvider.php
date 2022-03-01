<?php

namespace Sitepilot\Plugin\Providers;

use Sitepilot\Framework\Support\ServiceProvider;
use Sitepilot\Plugin\Services\ClientRoleService;

class ClientRoleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap application services and hooks.
     */
    public function boot(): void
    {
        $this->add_action('admin_init', '@update_role');
    }

    /**
     * Update client role capabilities.
     */
    public function update_role(ClientRoleService $client_role)
    {
        if (!$client_role->enabled()) {
            remove_role('sitepilot_user');
            return;
        }

        $capabilities = get_role('administrator')->capabilities;
        $exclude_capabilities = $client_role->excluded_capabilities();

        foreach ($capabilities as $key => $value) {
            if (!in_array($key, $exclude_capabilities)) {
                $role_capabilities[$key] = $value;
            }
        }

        add_role(
            'sitepilot_user',
            sprintf(__('%s Client', 'sitepilot'), $this->app->branding->name()),
            $role_capabilities
        );
    }
}
