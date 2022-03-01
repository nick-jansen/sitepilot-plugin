<?php

namespace Sitepilot\Framework\Traits;

trait HasShortcodes
{
    /**
     * Add namespaced shortcode.
     *
     * @param callable|string $callback
     */
    public function add_shortcode(string $tag, $callback): void
    {
        if (is_string($callback)) {
            $callback = [$this, $callback];
        }

        if (!empty($this->app)) {
            $app = $this->app;
        } else {
            $app = $this;
        }

        add_shortcode($app->namespace($tag, '_'), $callback);
    }
}
