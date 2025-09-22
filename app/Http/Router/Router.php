<?php
namespace SmartySoft\AdjusterForge\Http\Router;

use WP_REST_Server;
use WP_Error;

/**
 * Class Router
 *
 * This class is responsible for registering routes for the application.
 * It provides methods to register routes for different HTTP request methods such as GET, POST, and PUT.
 *
 * @package SmartySoft\AdjusterForge\Http\Router
 */
class Router
{
    /**
     * Register a new route for GET requests.
     *
     * @param string $url The route URL.
     * @param string $action The controller method in the format "Controller@method".
     */
    public static function get($url, $action)
    {
        self::registerRoute($url, $action, WP_REST_Server::READABLE);
    }

    /**
     * Register a new route for POST requests.
     *
     * @param string $url The route URL.
     * @param string $action The controller method in the format "Controller@method".
     */
    public static function post($url, $action)
    {
        self::registerRoute($url, $action, WP_REST_Server::CREATABLE);
    }

    /**
     * Register a new route for PUT requests.
     *
     * @param string $url The route URL.
     * @param string $action The controller method in the format "Controller@method".
     */
    public static function put($url, $action)
    {
        self::registerRoute($url, $action, WP_REST_Server::EDITABLE);
    }

    /**
     * Register a new route for DELETE requests.
     *
     * @param string $url The route URL.
     * @param string $action The controller method in the format "Controller@method".
     */
    public static function delete($url, $action)
    {
        self::registerRoute($url, $action, WP_REST_Server::DELETABLE);
    }

    public static function publicGet($url, $action)
    {
        self::registerPublicRoute($url, $action, WP_REST_Server::READABLE);
    }

    public static function publicPost($url, $action)
    {
        self::registerPublicRoute($url, $action, WP_REST_Server::CREATABLE);
    }

    /**
     * Register a new route.
     *
     * @param string $url The route URL.
     * @param string $action The controller method in the format "Controller@method".
     * @param string $type The HTTP request type (e.g., READABLE, CREATABLE, EDITABLE, DELETABLE).
     */
    private static function registerRoute($url, $action, $type)
    {
        // Replace route parameters with regex patterns.
        $url = preg_replace( '/\{(\w+)\}/', '(?P<$1>[^\/]+)', $url );
        
        list($controller, $method) = self::parseAction( $action );

        // Ensure the controller class and method exist before registering.
        if ( ! class_exists($controller) || ! method_exists( $controller, $method ) ) {
            return;
        }

        $controllerInstance = new $controller();

        if ( ! method_exists($controllerInstance, $method ) ) {
            return;
        }

        register_rest_route(
            'driver-forge/v2',
            $url,
            [
                'methods' => $type,
                'callback' => [$controllerInstance, $method],
                'permission_callback' => function () {
                    if (!isset($_SERVER['HTTP_X_WP_NONCE']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_SERVER['HTTP_X_WP_NONCE'])), 'wp_rest')) {
                        return new WP_Error('rest_forbidden', __('You do not have permission to access this resource.', 'driver-forge'), ['status' => 403]);
                    }
                    return true;
                },
                'args' => self::getArgsFromUrl($url),
            ]
        );
    }

    private static function registerPublicRoute($url, $action, $type)
    {
        // Replace route parameters with regex patterns.
        $url = preg_replace( '/\{(\w+)\}/', '(?P<$1>[^\/]+)', $url );
        
        list($controller, $method) = self::parseAction( $action );

        // Ensure the controller class and method exist before registering.
        if ( ! class_exists($controller) || ! method_exists( $controller, $method ) ) {
            return;
        }

        $controllerInstance = new $controller();

        if ( ! method_exists($controllerInstance, $method ) ) {
            return;
        }

        register_rest_route(
            'driver-forge/v2',
            $url,
            [
                'methods' => $type,
                'callback' => [$controllerInstance, $method],
                'permission_callback' => '__return_true',
                'args' => self::getArgsFromUrl($url),
            ]
        );
    }


    /**
     * Extract arguments from the URL and define their validation and sanitization callbacks.
     *
     * @param string $url The route URL.
     * @return array The arguments array.
     */
    private static function getArgsFromUrl($url)
    {
        preg_match_all( '/\(\?P<(\w+)>/', $url, $matches );
        $args = [];

        foreach ($matches[1] as $param) {
            $args[$param] = [
                'validate_callback' => function ($param) {
                    return is_string($param) || is_numeric($param);
                },
                'sanitize_callback' => function ($param) {
                    return is_numeric($param) ? intval($param) : sanitize_text_field($param);
                },
            ];
        }

        return $args;
    }

    /**
     * Parse the controller and method from the action string.
     *
     * @param string $action The action in the format "Controller@method".
     * @return array [$controller, $method]
     */
    private static function parseAction($action) {
        list($controller, $method) = explode('@', $action);
        
        // Get the controller namespace from composer.json PSR-4 autoload configuration
        $namespace = self::getControllerNamespace();
        $controller = $namespace . '\\' . $controller;
        
        return array($controller, $method);
    }

    /**
     * Get the controller namespace from composer.json PSR-4 autoload configuration.
     *
     * @return string The namespace for controllers
     */
    private static function getControllerNamespace() {
        // Default namespace in case we can't find composer.json or the configuration
        $defaultNamespace = 'SmartySoft\\AdjusterForge\\Http\\Controller';
        
        // Path to composer.json relative to this file
        $composerJsonPath = dirname(__FILE__, 4) . '/composer.json';
        
        if (!file_exists($composerJsonPath)) {
            return $defaultNamespace;
        }
        
        $composerJson = json_decode(file_get_contents($composerJsonPath), true);
        
        if (!$composerJson || !isset($composerJson['autoload']['psr-4'])) {
            return $defaultNamespace;
        }
        
        // Look for the controller namespace in PSR-4 mapping
        foreach ($composerJson['autoload']['psr-4'] as $namespace => $path) {
            // Remove trailing slash from namespace
            $namespace = rtrim($namespace, '\\');
            
            // Check if this is potentially the controllers namespace
            if (strpos($path, 'app') !== false || strpos($namespace, 'Http') !== false) {
                return $namespace . '\\Http\\Controller';
            }
        }
        
        // If no specific controller namespace found, use the first namespace and append controller part
        reset($composerJson['autoload']['psr-4']);
        $baseNamespace = rtrim(key($composerJson['autoload']['psr-4']), '\\');
        return $baseNamespace . '\\Http\\Controller';
    }
}