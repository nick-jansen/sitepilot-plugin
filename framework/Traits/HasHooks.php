<?php

namespace Sitepilot\Framework\Traits;

trait HasHooks
{
    /**
     * Adds a callback to a filter hook.
     */
    public function add_filter(string $hook, $callback, ...$args): void
    {
        if (is_string($callback)) {
            $callback = [$this, $callback];
        }

        add_filter($hook, $callback, ...$args);
    }

    /**
     * Returns a value to a filter hook.
     *
     * @param mixed $value
     */
    public function add_filter_value(string $hook, $value, ...$args): void
    {
        add_filter($hook, function () use ($value) {
            return $value;
        }, ...$args);
    }

    /**
     * Adds a callback to an action hook.
     *
     * @param mixed $callback
     */
    public function add_action(string $hook, $callback, int $priority = 10, int $accepted_args = 1): void
    {
        if (is_string($callback) && substr($callback, 0, 1) == '@') {
            add_action($hook, function () use ($callback) {
                $this->call(substr($callback, 1));
            }, $priority, $accepted_args);

            return;
        }

        if (is_string($callback)) {
            $callback = [$this, $callback];
        }

        add_action($hook, $callback, $priority, $accepted_args);
    }
}
