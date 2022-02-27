<?php

namespace Sitepilot\Framework\Traits;

use Sitepilot\Framework\Illuminate\Container\Container;

trait InteractsWithContainer
{
    /**
     * The container instance.
     * 
     * @var Container
     */
    protected Container $container;

    /**
     * Register an existing instance as shared in the container.
     *
     * @param string $abstract
     * @param mixed $instance
     * @return mixed
     */
    public function instance(...$args)
    {
        return $this->container->instance(...$args);
    }

    /**
     * Register a binding with the container.
     *
     * @param  string  $abstract
     * @param  \Closure|string|null  $concrete
     * @param  bool  $shared
     * @return void
     *
     * @throws \TypeError
     */
    public function bind(...$args)
    {
        return $this->container->bind(...$args);
    }

    /**
     * Register a shared binding in the container.
     *
     * @param  string  $abstract
     * @param  \Closure|string|null  $concrete
     * @return void
     */
    public function singleton(...$args)
    {
        return $this->container->singleton(...$args);
    }

    /**
     * Call the given Closure / class@method and inject its dependencies.
     *
     * @param  callable|string  $callback
     * @param  array<string, mixed>  $parameters
     * @param  string|null  $defaultMethod
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function call(...$args)
    {
        return $this->container->call(...$args);
    }

    /**
     * Alias a type to a different name.
     *
     * @param  string  $abstract
     * @param  string  $alias
     * @return void
     *
     * @throws \LogicException
     */
    public function alias(...$args)
    {
        return $this->container->alias(...$args);
    }

    /**
     * Resolve the given type from the container.
     *
     * @param  string|callable  $abstract
     * @param  array  $parameters
     * @param  bool  $raiseEvents
     * @return mixed
     *
     * @throws \Sitepilot\Framework\Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Sitepilot\Framework\Illuminate\Contracts\Container\CircularDependencyException
     */
    public function get(...$args)
    {
        return $this->container->get(...$args);
    }

    /**
     * Dynamically access container services.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->container->get($key);
    }
}
