<?php

namespace Sitepilot\Framework\Traits;

use Sitepilot\Framework\Support\ServiceProvider;

trait InteractsWithProviders
{
    use InteractsWithContainer;

    /**
     * All of the registered service providers.
     *
     * @var ServiceProvider[]
     */
    protected $registered_providers = [];

    /**
     * Get the registered service provider instance if it exists.
     *
     * @param ServiceProvider|string $provider
     * @return ServiceProvider[]|null
     */
    public function get_provider($provider)
    {
        return array_values($this->get_providers($provider))[0] ?? null;
    }

    /**
     * Get the registered service provider instances if any exist.
     *
     * @param ServiceProvider|string $provider
     */
    public function get_providers($provider): array
    {
        $name = is_string($provider) ? $provider : get_class($provider);

        return array_filter($this->registered_providers, function ($value) use ($name) {
            return $value instanceof $name;
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Resolve a service provider instance from the class name.
     */
    protected function resolve_provider(string $provider): ServiceProvider
    {
        return new $provider($this);
    }

    /**
     * Boot service providers.
     */
    protected function boot_providers(): void
    {
        array_walk($this->registered_providers, function ($p) {
            $this->boot_provider($p);
        });
    }

    /**
     * Boot the given service provider.
     */
    protected function boot_provider(ServiceProvider $provider): void
    {
        $provider->call_booting_callbacks();

        if (method_exists($provider, 'boot')) {
            $this->call([$provider, 'boot']);
        }

        $provider->call_booted_callbacks();
    }

    /**
     * Mark the given provider as registered.
     */
    protected function mark_as_registered(ServiceProvider $provider): void
    {
        $this->registered_providers[] = $provider;
    }
}
