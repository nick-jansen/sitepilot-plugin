<?php

namespace Sitepilot\Plugin\Services;

use Sitepilot\Framework\Foundation\Application;

class BrandingService
{
    /**
     * The application instance.
     */
    private Application $app;

    /**
     * Create a new branding service instance.
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Determine if the branding is enabled.
     */
    public function enabled(): bool
    {
        return $this->app->filter('branding/enabled', false);
    }

    /**
     * Get the branding name.
     */
    public function name(): string
    {
        return $this->app->filter('branding/name', 'Sitepilot');
    }

    /**
     * Get the branding powered by text.
     */
    public function powered_by(): string
    {
        return $this->app->filter('branding/powered_by', 'Sitepilot');
    }

    /**
     * Get the branding website.
     */
    public function website(): string
    {
        return $this->app->filter('branding/website', 'https://sitepilot.io');
    }

    /**
     * Get the branding logo.
     */
    public function logo(): string
    {
        return $this->app->filter('branding/logo', $this->app->public_url('img/sitepilot-logo.png'));
    }

    /**
     * Get the branding icon.
     */
    public function icon(): string
    {
        return $this->app->filter('branding/icon', $this->app->public_url('img/sitepilot-icon.png'));
    }

    /**
     * Get the branding support email.
     */
    public function support_email(): string
    {
        return $this->app->filter('branding/support_email', 'support@sitepilot.io');
    }

    /**
     * Get the branding support website.
     */
    public function support_website(): string
    {
        return $this->app->filter('branding/support_website', 'https://support.sitepilot.nl');
    }

    /**
     * Get the branding support widget.
     */
    public function support_widget(): string
    {
        return $this->app->filter(
            'branding/support_widget',
            '<script type="text/javascript">
                ! function(e, t, n) {
                    function a() {
                        var e = t.getElementsByTagName("script")[0],
                            n = t.createElement("script");
                        n.type = "text/javascript", n.async = !0, n.src = "https://beacon-v2.helpscout.net", e.parentNode.insertBefore(n, e)
                    }
                    if (e.Beacon = n = function(t, n, a) {
                            e.Beacon.readyQueue.push({
                                method: t,
                                options: n,
                                data: a
                            })
                        }, n.readyQueue = [], "complete" === t.readyState) return a();
                    e.attachEvent ? e.attachEvent("onload", a) : e.addEventListener("load", a, !1)
                }(window, document, window.Beacon || function() {});
            </script>
            <script type="text/javascript">
                window.Beacon(\'init\', \'43962daf-3958-4eea-b3d8-b030020fb2ce\')
            </script>'
        );
    }
}
