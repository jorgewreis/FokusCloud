<?php

return [

    /*
    |--------------------------------------------------------------------------
    | View Storage Paths
    |--------------------------------------------------------------------------
    |
    | Most screens in this project are static HTML files served from public/.
    | Laravel still expects a valid view path when optimization commands cache
    | framework metadata, so the default resources/views directory is kept.
    |
    */

    'paths' => [
        resource_path('views'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Compiled View Path
    |--------------------------------------------------------------------------
    |
    | This must be a string path, not realpath(...), because fresh deploys may
    | create the directory immediately before running artisan optimize.
    |
    */

    'compiled' => env(
        'VIEW_COMPILED_PATH',
        storage_path('framework/views')
    ),

];
