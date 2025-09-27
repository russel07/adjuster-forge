<?php

namespace SmartySoft\AdjusterForge\Hooks\Handler;
use SmartySoft\AdjusterForge\Database\DBMigrate;

/**
 * Class ActivationHandler
 *
 * Handles plugin activation tasks.
 */
class ActivationHandler
{
    /**
     * Handle plugin activation.
     *
     * This method is called when the plugin is activated.
     * It runs database migrations and updates the plugin version option.
     */
    public function handle()
    {
        // Run database migrations.
        DBMigrate::run();

        // Activate the plugin.
        $this->activate();
        
        if (! wp_next_scheduled('adjuster_forge_daily_tasks')) {
            wp_schedule_event(time(), 'daily', 'adjuster_forge_daily_tasks');
        }
    }

    /**
     * Activate the plugin.
     *
     * Sets the plugin version option if it doesn't exist.
     */
    public function activate()
    {
        $version = get_option('adjuster_forge_plugin_version');

        // Check if the plugin version option doesn't exist and set it.
        if ( ! $version ) {
            update_option('adjuster_forge_plugin_version', ADJUSTER_FORGE_VERSION);
        }

        add_role( 'adjuster', 'Adjuster', [
            'read' => true,
            'upload_files' => true,
        ] );

        add_role( 'company', 'Company', [
            'read' => true,
        ] );

        flush_rewrite_rules();
    }
}