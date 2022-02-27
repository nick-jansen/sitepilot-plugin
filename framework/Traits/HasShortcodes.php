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

        add_shortcode($this->namespace($tag, '_'), $callback);
    }
}
