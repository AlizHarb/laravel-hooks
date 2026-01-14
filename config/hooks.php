<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Hook Caching
    |--------------------------------------------------------------------------
    |
    | To improve performance in production, you can cache discovered hooks.
    |
    */
    'cache' => [
        'enabled' => env('HOOK_CACHE_ENABLED', false),
        'key' => 'laravel_hooks_map',
        'store' => 'file',
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto Discovery
    |--------------------------------------------------------------------------
    |
    | The paths where the package should look for Hook attributes.
    |
    */
    'scan_paths' => [
        app_path('Hooks'),
        app_path('Listeners'),
        // modules_path(), // Example if using nWidart/laravel-modules
    ],

    /*
    |--------------------------------------------------------------------------
    | Debugging
    |--------------------------------------------------------------------------
    |
    | Enable hook inspection and snapshots for debugging purposes.
    |
    */
    'debug' => env('HOOK_DEBUG', false),
];
