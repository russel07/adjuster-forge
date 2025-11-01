<?php
defined('ABSPATH') || exit; // Prevent direct access
/**
 * Plugin Name:       Adjuster Forge
 * Description:       A simple plugin to manage adjusters and companies.
 * Version:           1.0.4
 * Requires at least: 5.2
 * Requires PHP:      7.4
 * Author:            Md. Russel Hussain
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       adjuster-forge
 * Domain Path:       /languages
 */

const ADJUSTER_FORGE_PLUGIN_ASSET_ID = 'adjuster-forge';
const ADJUSTER_FORGE_PLUGIN_ASSET_VARS = 'adjuster_forge';

// Define the plugin constant PLUGIN_VERSION if not already defined.
defined('ADJUSTER_FORGE_VERSION') || define('ADJUSTER_FORGE_VERSION', '1.0.4');

// Define the plugin constant PLUGIN_PATH if not already defined.
defined('ADJUSTER_FORGE_PLUGIN_PATH') || define('ADJUSTER_FORGE_PLUGIN_PATH', __FILE__);

// Define the plugin constant PLUGIN_DIR if not already defined.
defined('ADJUSTER_FORGE_PLUGIN_DIR') || define('ADJUSTER_FORGE_PLUGIN_DIR', __DIR__);

// Define the plugin constant PLUGIN_URL if not already defined.
defined('ADJUSTER_FORGE_PLUGIN_URL') || define('ADJUSTER_FORGE_PLUGIN_URL',  plugin_dir_url(__FILE__));

// Define the plugin constant PLUGIN_BASE_PATH if not already defined.
defined('ADJUSTER_FORGE_PLUGIN_BASE_PATH') || define('ADJUSTER_FORGE_PLUGIN_BASE_PATH',  plugin_dir_path(__FILE__));

// Check for the autoloader file's existence and include it.
if ( file_exists(__DIR__ . '/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    wp_die( esc_html__( 'Autoload file is missing.', 'adjuster-forge' ) );
}

// Register activation hook with a callable array method.
register_activation_hook( __FILE__, array('SmartySoft\AdjusterForge\Config\Bootstrap', 'activate_me' ) );

// Register deactivation hook to remove custom user roles.
register_deactivation_hook(__FILE__, array('SmartySoft\AdjusterForge\Config\Bootstrap', 'deactivate_me' ) );

/**
 * Initializes the DriveForYou plugin.
 */
function smarty_soft_adjuster_forge_init()
{
    if (class_exists('SmartySoft\AdjusterForge\Config\Bootstrap')) {
        $bootstrap = new SmartySoft\AdjusterForge\Config\Bootstrap();
        $bootstrap->boot();
    } else {
        wp_die( esc_html__( 'Drive for you plugin is not properly installed.', 'adjuster-forge' ) );
    }
}

add_action( 'plugins_loaded', 'smarty_soft_adjuster_forge_init' );