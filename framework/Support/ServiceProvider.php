<?php

namespace Sitepilot\Framework\Support;

use Sitepilot\Framework\Traits\HasHooks;
use Sitepilot\Framework\Traits\HasShortcodes;
use Sitepilot\Framework\Foundation\Application;

abstract class ServiceProvider
{
    use HasHooks, HasShortcodes;

    /**
     * The application instance.
     */
    protected Application $app;

    /**
     * All of the registered register callbacks.
     */
    protected array $register_callbacks = [];

    /**
     * All of the registered booting callbacks.
     */
    protected array $booting_callbacks = [];

    /**
     * All of the registered booted callbacks.
     */
    protected array $booted_callbacks = [];

    /**
     * Create a new service provider instance.
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Register application services and filters.
     */
    public function register(): void
    {
        //
    }

    /**
     * Register a callback to be run after the "register" method is called.
     *
     * @param \Closure $callback
     * @return void
     */
    public function registered(\Closure $callback)
    {
        $this->register_callbacks[] = $callback;
    }

    /**
     * Register a callback to be run before the "boot" method is called.
     *
     * @param \Closure $callback
     * @return void
     */
    public function booting(\Closure $callback)
    {
        $this->booting_callbacks[] = $callback;
    }

    /**
     * Register a callback to be run after the "boot" method is called.
     *
     * @param \Closure $callback
     * @return void
     */
    public function booted(\Closure $callback)
    {
        $this->booted_callbacks[] = $callback;
    }

    /**
     * Call the register callbacks.
     */
    public function call_register_callbacks(): void
    {
        foreach ($this->register_callbacks as $callback) {
            $this->app->call($callback);
        }
    }

    /**
     * Call the booting callbacks.
     */
    public function call_booting_callbacks(): void
    {
        foreach ($this->booting_callbacks as $callback) {
            $this->app->call($callback);
        }
    }

    /**
     * Call the booted callbacks.
     */
    public function call_booted_callbacks(): void
    {
        foreach ($this->booted_callbacks as $callback) {
            $this->app->call($callback);
        }
    }

    /**
     * Call the given method and inject its dependencies.
     *
     * @param  callable|string  $callback
     * @param  array<string, mixed>  $parameters
     * @param  string|null  $defaultMethod
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function call($callback, array $parameters = [], $default_method = null)
    {
        return $this->app->call([$this, $callback], $parameters, $default_method);
    }
}
