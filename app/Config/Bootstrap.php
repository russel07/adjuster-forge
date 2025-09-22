<?php

namespace SmartySoft\AdjusterForge\Config;
use SmartySoft\AdjusterForge\Foundation\Application;
use SmartySoft\AdjusterForge\Hooks\Handler\ActivationHandler;
use SmartySoft\AdjusterForge\Hooks\Handler\DeactivationHandler;
use SmartySoft\AdjusterForge\Cpt\Jobs;
/**
 * Class Bootstrap
 *
 * Bootstraps the WPSS Social Share plugin.
 */
class Bootstrap
{
    /**
     * Bootstraps the plugin.
     */
    public function boot()
    {
        // Include REST API routes.
        add_action('rest_api_init', function () {
            require_once DRIVER_FORGE_PLUGIN_BASE_PATH . 'app/Http/Router/Api.php';
        });

        new Jobs();
        
        $app = Application::instance();
        $app->load();
    }

    /**
     * Handles plugin activation.
     */
    public static function activate_me()
    {
        // Handle plugin activation.
        (new ActivationHandler())->handle();
    }

    public static function deactivate_me()
    {
        // Handle plugin deactivation.
        (new DeactivationHandler())->handle();
    }
}