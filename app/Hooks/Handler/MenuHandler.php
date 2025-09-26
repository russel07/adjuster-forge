<?php

namespace SmartySoft\AdjusterForge\Hooks\Handler;

defined('ABSPATH') || exit; // Prevent direct access

class MenuHandler
{
    /**
     * Initialize the menu.
     *
     * This method sets up the main menu page and submenu pages.
     */
    public static function init()
    {
        // Add the main menu page.
        add_menu_page(
            __('', 'adjuster-forge'), // Page Title
            __('Adjuster Forge', 'adjuster-forge'), // Menu Title
            'manage_options', // Capability
            'adjuster-forge', // Menu Slug
            [new self(), 'loadView'], // Callback
            'dashicons-layout', // Dashicon
            56 // Position
        );

        // Add submenus
        self::add_submenus();
    }

    /**
     * Add submenus under the main menu.
     */
    public static function add_submenus()
    {
        // Add home page as a submenu
        add_submenu_page(
            'adjuster-forge',
            __( 'Adjuster Forge', 'adjuster-forge' ),
            __( 'Dashboard', 'adjuster-forge' ),
            'manage_options',
            'adjuster-forge',
            [new self(), 'loadView']
        );
    }

    /**
     * Load the admin view.
     *
     * This method includes the admin view file.
     */
    public function loadView()
    { 
        // Load the admin view.
        include_once(dirname(ADJUSTER_FORGE_PLUGIN_PATH) . '/app/views/admin.php');
    }
}