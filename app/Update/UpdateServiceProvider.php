<?php

namespace Sitepilot\Plugin\Update;

use Sitepilot\Framework\Support\ServiceProvider;

class UpdateServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap application services and hooks.
     */
    public function boot(): void
    {
        $this->add_action('init', '@build_update_checker', 99);
    }

    /**
     * Build the update checker.
     */
    public function build_update_checker(UpdateService $update): void
    {
        foreach ($update->apps() as $app) {
            if ($app->is_dev()) return;

            \Puc_v4_Factory::buildUpdateChecker(
                $update->repo($app->namespace()),
                $app->file(),
                $app->namespace()
            );
        }
    }
}
