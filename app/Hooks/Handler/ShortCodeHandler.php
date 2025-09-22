<?php

namespace SmartySoft\AdjusterForge\Hooks\Handler;
defined('ABSPATH') || exit; // Prevent direct access

use SmartySoft\AdjusterForge\Foundation\AppHelper;

class ShortCodeHandler
{
    use AppHelper;

    public function __construct()
    {
        add_shortcode('driver_forge_profile', [$this, 'render_driver_forge_profile'] );
        add_shortcode('driver_forge_auth', [$this, 'af_render_driver_forge_auth' ] );
    }

    public function render_driver_forge_profile()
    {
        $this->load_driver_forge_profile_assets();
        ob_start(); // Start output buffering
        echo '<div id="driver_forge_profile_all_tabs"></div>';
        return ob_get_clean(); // Return the buffered content
    }

    public function load_driver_forge_profile_assets()
    {
        $setings = self::getOption('driver_forge_payment_settings', []);
        $payment_gateway = isset($setings['payment_gateway']) ? $setings['payment_gateway'] : '';

        // Enqueue user and shortcode area
        wp_enqueue_style(
            DRIVER_FORGE_PLUGIN_ASSET_ID . '-style',
            DRIVER_FORGE_PLUGIN_URL . 'assets/css/driver_forge_profile.css',
            [],
            DRIVER_FORGE_VERSION 
        );

        // Enqueue JavaScript for user and shortcode area
        wp_enqueue_script(
            DRIVER_FORGE_PLUGIN_ASSET_ID,
            DRIVER_FORGE_PLUGIN_URL . 'assets/js/driver_forge_profile.js',
            array('jquery', 'moment'),
            DRIVER_FORGE_VERSION, 
            true
        );
        wp_localize_script(DRIVER_FORGE_PLUGIN_ASSET_ID, DRIVER_FORGE_PLUGIN_ASSET_VARS . '_app_vars', $this->driver_forge_app_vars());
    }

    public function load_driver_forge_auth_assets()
    {

        // Enqueue user and shortcode area
        wp_enqueue_style(
            DRIVER_FORGE_PLUGIN_ASSET_ID . '-style',
            DRIVER_FORGE_PLUGIN_URL . 'assets/css/driver_forge_auth.css',
            [],
            DRIVER_FORGE_VERSION
        );

        // Enqueue JavaScript for user and shortcode area
        wp_enqueue_script(
            DRIVER_FORGE_PLUGIN_ASSET_ID,
            DRIVER_FORGE_PLUGIN_URL . 'assets/js/driver_forge_auth.js',
            array('jquery', 'moment'),
            DRIVER_FORGE_VERSION,
            true
        );
        wp_localize_script(DRIVER_FORGE_PLUGIN_ASSET_ID, DRIVER_FORGE_PLUGIN_ASSET_VARS . '_app_vars', $this->driver_forge_app_vars());
    }

    public function af_render_driver_forge_auth()
    {
        $this->load_driver_forge_auth_assets();
        ob_start(); // Start output buffering
        echo '<div id="driver_forge_profile_auth"></div>';
        return ob_get_clean(); // Return the buffered content
    }

    /**
     * Enqueue the necessary scripts and styles for the frontend.
     */
    public function enqueue_jobs_scripts($atts)
    {
        wp_enqueue_style(
            DRIVER_FORGE_PLUGIN_ASSET_ID . '-style',
            DRIVER_FORGE_PLUGIN_URL . 'assets/css/driver_jobs.css',
            [],
            DRIVER_FORGE_VERSION
        );
        wp_enqueue_script(
            DRIVER_FORGE_PLUGIN_ASSET_ID . '-load-more-jobs',
            DRIVER_FORGE_PLUGIN_URL . 'assets/js/load-more-jobs.js',
            [ 'jquery' ],
            DRIVER_FORGE_VERSION,
            true
        );
        
        // Localize script to pass AJAX data
        wp_localize_script(
            DRIVER_FORGE_PLUGIN_ASSET_ID . '-load-more-jobs',
            'saJobsParams',
            [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('driver_jobs_nonce'),
                'atts'    => $atts
            ]
        );
    }
}
