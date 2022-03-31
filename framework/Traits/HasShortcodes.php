<?php

namespace Sitepilot\Framework\Traits;

trait HasShortcodes
{
    /**
     * The shortcode namespace.
     * 
     * @var ?string
     */
    protected $shortcode_namespace = null;

    /**
     * Add namespaced shortcode.
     *
     * @param callable|string $callback
     */
    public function add_shortcode(string $tag, $callback, $namespaced = true): void
    {
        if (is_string($callback)) {
            $callback = [$this, $callback];
        }

        if (!empty($this->app)) {
            $app = $this->app;
        } else {
            $app = $this;
        }

        add_shortcode(
            $this->shortcode_namespace
                ? $this->shortcode_namespace . '_' . $tag
                : $app->namespace($tag, '_'),
            $callback
        );
    }
}
