<?php

namespace Sitepilot\Framework\Foundation;

use Sitepilot\Framework\Traits\HasHooks;
use Sitepilot\Framework\Traits\HasShortcodes;
use Sitepilot\Framework\Support\ServiceProvider;
use Sitepilot\Framework\Traits\InteractsWithAdmin;
use Sitepilot\Framework\Traits\InteractsWithContainer;
use Sitepilot\Framework\Traits\InteractsWithProviders;
use Sitepilot\Framework\Illuminate\Container\Container;

class Application
{
    use HasHooks,
        HasShortcodes,
        InteractsWithAdmin,
        InteractsWithContainer,
        InteractsWithProviders;

    /**
     * The cached application version.
     */
    protected ?string $version = null;

    /**
     * The registered app instances.
     *
     * @var array
     */
    static protected array $loaded_apps = [];

    /**
     * The path to the application boot file.
     */
    protected string $file;

    /**
     * The base path to the application.
     */
    protected string $base_path;

    /**
     * The base url to the application.
     */
    protected string $base_url;

    /**
     * The application namespace.
     */
    protected string $namespace;

    /**
     * The application boot hook.
     */
    protected string $boot_hook = 'after_setup_theme';

    /**
     * Indicates if the application has "booted".
     */
    protected bool $booted = false;

    /**
     * All of the registered applications.
     *
     * @var Application[]
     */
    protected static $registered_apps = [];

    /**
     * Create a new application instance.
     */
    public function __construct(string $namespace, string $file, array $providers = [])
    {
        $this->container = new Container;

        $this->namespace = $namespace;

        $this->set_paths($file);

        foreach ($providers as $provider) {
            $this->register_provider($provider);
        }

        $this->instance(self::class, $this);

        self::$registered_apps[$namespace] = $this;

        $this->add_action($this->boot_hook, 'boot');

        $this->action('registered');
    }

    /**
     * Get registered application instance.
     */
    public static function app(string $namespace): ?Application
    {
        if (isset(self::$registered_apps[$namespace])) {
            return self::$registered_apps[$namespace];
        }

        return null;
    }

    /**
     * Get registered applications.
     * 
     * @return Application[]
     */
    public static function apps()
    {
        return self::$registered_apps;
    }

    /**
     * Set the base paths for the application.
     */
    public function set_paths(string $file): void
    {
        $this->file = $file;

        if ($this->is_theme()) {
            $theme = wp_get_theme($this->namespace);
            $this->base_path = $theme->get_stylesheet_directory();
            $this->base_url = $theme->get_stylesheet_directory_uri();
        } else {
            $this->base_path = WP_PLUGIN_DIR . '/' . $this->namespace;
            $this->base_url = plugins_url($this->namespace);
        }
    }

    /**
     * Register a service provider with the application.
     *
     * @param  ServiceProvider|string  $provider
     * @param  bool  $force
     * @return ServiceProvider
     */
    public function register_provider($provider, $force = false)
    {
        if (($registered = $this->get_provider($provider)) && !$force) {
            return $registered;
        }

        // If the given "provider" is a string, we will resolve it, passing in the
        // application instance automatically for the developer. This is simply
        // a more convenient way of specifying your service provider classes.
        if (is_string($provider)) {
            $provider = $this->resolve_provider($provider);
        }

        $provider->register();

        // If there are bindings / singletons set as properties on the provider we
        // will spin through them and register them with the application, which
        // serves as a convenience layer while registering a lot of bindings.
        if (property_exists($provider, 'bindings')) {
            foreach ($provider->bindings as $key => $value) {
                if (is_numeric($key)) {
                    $this->bind($value);
                } else {
                    $this->bind($key, $value);
                }
            }
        }

        if (property_exists($provider, 'singletons')) {
            foreach ($provider->singletons as $key => $value) {
                if (is_numeric($key)) {
                    $this->singleton($value);
                } else {
                    $this->singleton($key, $value);
                }
            }
        }

        $this->mark_as_registered($provider);

        $provider->call_register_callbacks();

        // If the application has already booted, we will call this boot method on
        // the provider class so it has an opportunity to do its boot logic and
        // will be ready for any usage by this developer's application logic.
        if ($this->is_booted()) {
            $this->boot_provider($provider);
        }

        return $provider;
    }

    /**
     * Boot the application's service providers.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->is_booted()) {
            return;
        }

        $this->boot_providers();

        $this->booted = true;

        $this->action('booted');
    }

    /**
     * Get the namespaced path to the application.
     */
    public function namespace(string $path = '', string $separator = '/')
    {
        return $this->namespace . ($path ? $separator . $path : $path);
    }

    /**
     * Get the application version.
     */
    public function version(): ?string
    {
        if ($this->version) {
            return $this->version;
        }

        if ($this->is_plugin()) {
            $plugin = get_file_data($this->file, [
                'version' => 'Version'
            ], 'plugin');

            $version = $plugin['version'] ?? null;
        } else {
            $theme = wp_get_theme($this->namespace);
            $version = $theme->get('Version') ? $theme->get('Version') : null;
        }

        $this->version = $version;

        return $this->version;
    }

    /**
     * Returns the application script version.
     */
    public function script_version(): string
    {
        $version = $this->version();

        if ($this->is_dev()) {
            $version = time();
        }

        return $version;
    }

    /**
     * Get the path to the application boot file.
     */
    public function file(): string
    {
        return $this->file;
    }

    /**
     * Get the url path of the application.
     */
    public function url(string $path = ''): string
    {
        return $this->base_url . ($path ? '/' . $path : $path);
    }

    /**
     * Get the base path of the application.
     */
    public function path(string $path = ''): string
    {
        return $this->base_path . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Get the path to the public directory.
     */
    public function public_path(string $path = ''): string
    {
        return $this->filter('public_path', $this->path('public')) . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Get the url to the public directory.
     */
    public function public_url(string $path = ''): string
    {
        return $this->filter('public_url', $this->url('public')) . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Get the path to the resources directory.
     */
    public function resource_path(string $path = ''): string
    {
        return $this->filter('resource_path', $this->path('resources')) . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Get the path to the views directory.
     */
    public function view_path(string $path = ''): string
    {
        return $this->filter('view_path', $this->resource_path('views')) . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Get the path to the public / web directory.
     */
    public function lang_path(string $path = ''): string
    {
        return $this->filter('lang_path', $this->resource_path('lang')) . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Get the path to the storage directory.
     */
    public function storage_path(string $path = ''): string
    {
        return $this->filter('storage_path', wp_upload_dir()['basedir'] . DIRECTORY_SEPARATOR . $this->namespace()) . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Determine if the application is a plugin.
     */
    public function is_plugin(): bool
    {
        return !$this->is_theme();
    }

    /**
     * Determine if the application is a theme.
     */
    public function is_theme(): bool
    {
        return get_template() == $this->namespace
            || get_stylesheet() == $this->namespace;
    }

    /**
     * Determine if the application is in development mode.
     */
    public function is_dev(): bool
    {
        return strpos($this->version(), 'dev') !== false;
    }

    /**
     * Determine if the application has booted.
     *
     * @return bool
     */
    public function is_booted()
    {
        return $this->booted;
    }

    /**
     * Calls the callback functions that have been added to an action hook.
     */
    public function action(string $hook, ...$args): void
    {
        do_action($this->namespace($hook), ...$args);
    }

    /**
     * Calls the callback functions that have been added to a filter hook.
     *
     * @param mixed $value
     */
    public function filter(string $hook, $value)
    {
        return apply_filters($this->namespace($hook), $value);
    }

    /**
     * Get the contents of a template.
     */
    public function template(string $template, string $name = null, array $args = array()): string
    {
        if ($this->is_theme()) {
            ob_start();
            get_template_part($template, $name, array_merge(['app' => $this], $args));
            $template = ob_get_contents();
            ob_end_clean();

            return $template;
        } else {
            #toDo
            return '';
        }
    }
}
