<?php
    //Prevent direct access
    defined('ABSPATH') || exit;

    use SmartySoft\AdjusterForge\Hooks\Handler\FilterHooksHandler;

    //Add custom query vars
    add_filter('query_vars',  [ FilterHooksHandler::class, 'driver_forge_query_vars'] );