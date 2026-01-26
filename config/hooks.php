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
        // modules_path(), // Example if using alizharb/laravel-modular
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

    /*
    |--------------------------------------------------------------------------
    | Strict Mode
    |--------------------------------------------------------------------------
    |
    | Throw an exception if a hook is called with no listeners.
    |
    */
    'strict' => env('HOOK_STRICT', false),

    /*
    |--------------------------------------------------------------------------
    | Eloquent Bridge
    |--------------------------------------------------------------------------
    |
    | Automatically convert Eloquent events into Hook actions.
    |
    */
    'eloquent_bridge' => [
        'enabled' => env('HOOK_ELOQUENT_BRIDGE', true),
        'events' => [
            'eloquent.saved*',
            'eloquent.created*',
            'eloquent.deleted*',
        ],
        'except_events' => [],
        'models' => [
            // 'App\Models\*',
        ],
        'except_models' => [
            // 'App\Models\Job',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Filament Bridge
    |--------------------------------------------------------------------------
    |
    | Automatically create hooks for Filament components.
    |
    */
    'filament_bridge' => [
        'enabled' => env('HOOK_FILAMENT_BRIDGE', false),
        'tables' => true,
        'forms' => true,
        'pages' => true,
        'widgets' => true,
        'relation_managers' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire Bridge
    |--------------------------------------------------------------------------
    |
    | Automatically bridge global Livewire lifecycle events to hooks.
    |
    */
    'livewire_bridge' => [
        'enabled' => env('HOOK_LIVEWIRE_BRIDGE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Behaviors
    |--------------------------------------------------------------------------
    |
    */
    'graceful_by_default' => false,

    /*
    | Maximum depth for recursive hook calls to prevent infinite loops.
    */
    'max_nesting' => 50,

    /*
    | Whether to record hook metrics in Laravel Pulse if installed.
    */
    'pulse_enabled' => true,
];
