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

    public static function adjuster_forge_query_vars( $vars ) {
        $vars[] = 'adjuster_forge_download_file';
        return $vars;
    }
}
