<?php

namespace SmartySoft\AdjusterForge\Hooks\Handler;
defined('ABSPATH') || exit; // Prevent direct access

use SmartySoft\AdjusterForge\Foundation\AppHelper;

class ShortCodeHandler
{
    use AppHelper;

    public function __construct()
    {
        add_shortcode('adjuster_forge_profile', [$this, 'render_adjuster_forge_profile'] );
        add_shortcode('adjuster_forge_auth', [$this, 'af_render_adjuster_forge_auth' ] );
    }

    public function render_adjuster_forge_profile()
    {
        $this->load_adjuster_forge_profile_assets();
        ob_start(); // Start output buffering
        echo '<div id="adjuster_forge_profile_all_tabs"></div>';
        return ob_get_clean(); // Return the buffered content
    }

    public function load_adjuster_forge_profile_assets()
    {
        $setings = self::getOption('adjuster_forge_payment_settings', []);
        $payment_gateway = isset($setings['payment_gateway']) ? $setings['payment_gateway'] : '';

        // Enqueue user and shortcode area
        wp_enqueue_style(
            ADJUSTER_FORGE_PLUGIN_ASSET_ID . '-style',
            ADJUSTER_FORGE_PLUGIN_URL . 'assets/css/adjuster_forge_profile.css',
            [],
            ADJUSTER_FORGE_VERSION 
        );

        // Enqueue JavaScript for user and shortcode area
        wp_enqueue_script(
            ADJUSTER_FORGE_PLUGIN_ASSET_ID,
            ADJUSTER_FORGE_PLUGIN_URL . 'assets/js/adjuster_forge_profile.js',
            array('jquery', 'moment'),
            ADJUSTER_FORGE_VERSION, 
            true
        );
        wp_localize_script(ADJUSTER_FORGE_PLUGIN_ASSET_ID, ADJUSTER_FORGE_PLUGIN_ASSET_VARS . '_app_vars', $this->adjuster_forge_app_vars());
    }

    public function load_adjuster_forge_auth_assets()
    {

        // Enqueue user and shortcode area
        wp_enqueue_style(
            ADJUSTER_FORGE_PLUGIN_ASSET_ID . '-style',
            ADJUSTER_FORGE_PLUGIN_URL . 'assets/css/adjuster_forge_auth.css',
            [],
            ADJUSTER_FORGE_VERSION
        );

        // Enqueue JavaScript for user and shortcode area
        wp_enqueue_script(
            ADJUSTER_FORGE_PLUGIN_ASSET_ID,
            ADJUSTER_FORGE_PLUGIN_URL . 'assets/js/adjuster_forge_auth.js',
            array('jquery', 'moment'),
            ADJUSTER_FORGE_VERSION,
            true
        );
        wp_localize_script(ADJUSTER_FORGE_PLUGIN_ASSET_ID, ADJUSTER_FORGE_PLUGIN_ASSET_VARS . '_app_vars', $this->adjuster_forge_app_vars());
    }

    public function af_render_adjuster_forge_auth()
    {
        $this->load_adjuster_forge_auth_assets();
        ob_start(); // Start output buffering
        echo '<div id="adjuster_forge_profile_auth"></div>';
        return ob_get_clean(); // Return the buffered content
    }

    /**
     * Enqueue the necessary scripts and styles for the frontend.
     */
    public function enqueue_jobs_scripts($atts)
    {
        wp_enqueue_style(
            ADJUSTER_FORGE_PLUGIN_ASSET_ID . '-style',
            ADJUSTER_FORGE_PLUGIN_URL . 'assets/css/adjuster_jobs.css',
            [],
            ADJUSTER_FORGE_VERSION
        );
        wp_enqueue_script(
            ADJUSTER_FORGE_PLUGIN_ASSET_ID . '-load-more-jobs',
            ADJUSTER_FORGE_PLUGIN_URL . 'assets/js/load-more-jobs.js',
            [ 'jquery' ],
            ADJUSTER_FORGE_VERSION,
            true
        );
        
        // Localize script to pass AJAX data
        wp_localize_script(
            ADJUSTER_FORGE_PLUGIN_ASSET_ID . '-load-more-jobs',
            'saJobsParams',
            [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('adjuster_jobs_nonce'),
                'atts'    => $atts
            ]
        );
    }
}
