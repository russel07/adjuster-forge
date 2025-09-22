<?php

namespace SmartySoft\AdjusterForge\Foundation;
defined('ABSPATH') || exit; // Prevent direct access
use SmartySoft\AdjusterForge\Hooks\Handler\ActionHandler;
use SmartySoft\AdjusterForge\Hooks\Handler\MenuHandler;
use SmartySoft\AdjusterForge\Hooks\Handler\ShortCodeHandler;

class Application
{
    use AppHelper;
    use ActionHandler;

    /**
     * @var Application|null Instance of the Application class.
     */
    protected static $instance = null;

    /**
     * Application constructor.
     *
     * Initializes the Application class and enqueues assets for the plugin admin page.
     */
    public function __construct()
    {
        /*
         * If the current page is the plugin's admin page and the user is an admin,
         * enqueue scripts and styles using the 'load_admin_assets' method.
         */
        if ( isset($_GET['page']) && $_GET['page'] === DRIVER_FORGE_PLUGIN_ASSET_ID && is_admin() ) {
            add_action( 'admin_enqueue_scripts', [ $this, 'load_admin_assets' ], 1 );
        }

        (new ShortCodeHandler());
    }

    /**
     * Get the instance of the Application class.
     *
     * @return Application|null Instance of the Application class.
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Load and initialize the AMC plugin.
     *
     * Registers menu items, actions, and filters.
     */
    public function load()
    {
        if ( is_admin() ) {
            $this->addAction('admin_menu', MenuHandler::class, 'init', 10, 1);
        }

        require_once DRIVER_FORGE_PLUGIN_BASE_PATH . 'app/Hooks/actions.php';
        require_once DRIVER_FORGE_PLUGIN_BASE_PATH . 'app/Hooks/filters.php';

        $this->run();

    }

    /**
     * Enqueue assets for the plugin admin page.
     */
    public function load_admin_assets()
    {
        // Enqueue styles admin area
        wp_enqueue_style( DRIVER_FORGE_PLUGIN_ASSET_ID.'-style', DRIVER_FORGE_PLUGIN_URL . 'assets/css/driver_forge_admin.css' );

        // Enqueue JavaScript for admin
        wp_enqueue_script( DRIVER_FORGE_PLUGIN_ASSET_ID, DRIVER_FORGE_PLUGIN_URL . 'assets/js/driver_forge_admin.js', array('jquery', 'moment'), DRIVER_FORGE_VERSION, true);
        wp_localize_script( DRIVER_FORGE_PLUGIN_ASSET_ID, DRIVER_FORGE_PLUGIN_ASSET_VARS. '_app_vars', $this->driver_forge_app_vars());
    }
}