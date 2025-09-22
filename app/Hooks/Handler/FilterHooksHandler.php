<?php

namespace SmartySoft\AdjusterForge\Hooks\Handler;

defined('ABSPATH') || exit; // Prevent direct access
use SmartySoft\AdjusterForge\Foundation\AppHelper;

/**
 * FilterHooksHandler class
 * Define all action hooks methods
 */
class FilterHooksHandler
{
    use AppHelper;

    public static function driver_forge_query_vars( $vars ) {
        $vars[] = 'driver_forge_download_file';
        return $vars;
    }
}
