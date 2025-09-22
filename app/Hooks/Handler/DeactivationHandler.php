<?php

namespace SmartySoft\AdjusterForge\Hooks\Handler;

/**
 * Class DeactivationHandler
 *
 * Handles plugin deactivation tasks.
 */
class DeactivationHandler
{
    /**
     * Handle plugin deactivation.
     *
     * This method is called when the plugin is deactivated.
     * It runs database migrations and updates the plugin version option.
     */
    public function handle()
    {
        // Activate the plugin.
        $this->deactivate();
    }

    /**
     * Activate the plugin.
     *
     * Sets the plugin version option if it doesn't exist.
     */
    public function deactivate()
    {
        // Remove custom user roles.
        remove_role('driver');
        remove_role('company');

        $version = get_option('driver_forge_plugin_version');

        // If the plugin version option exists, delete it.
        if( $version ) {
            delete_option('driver_forge_plugin_version');
        }
        
        // Clear scheduled events if needed.
        wp_clear_scheduled_hook('driver_forge_daily_tasks');

        flush_rewrite_rules();
    }
}