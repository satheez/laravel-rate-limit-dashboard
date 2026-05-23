<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Enable / Disable Dashboard
    |--------------------------------------------------------------------------
    |
    | This option enables or disables the entire rate limit dashboard package.
    | When disabled, the middleware will not emit events and the dashboard
    | routes will not be accessible.
    |
    */
    'enabled' => env('RATE_LIMIT_DASHBOARD_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Storage Driver
    |--------------------------------------------------------------------------
    |
    | Supported: 'database', 'redis' (custom implementation required for redis)
    |
    */
    'storage' => env('RATE_LIMIT_STORAGE', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Event Retention Policy
    |--------------------------------------------------------------------------
    |
    | The number of days to retain raw rate limit events in the database.
    | Set to null to retain indefinitely.
    |
    */
    'retention_days' => env('RATE_LIMIT_RETENTION_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Default Limits
    |--------------------------------------------------------------------------
    |
    | Fallback limit values when no custom limiter is defined and a route
    | is throttled generically.
    |
    */
    'default_limits' => [
        'max_attempts' => env('RATE_LIMIT_DEFAULT_MAX_ATTEMPTS', 60),
        'decay_seconds' => env('RATE_LIMIT_DEFAULT_DECAY', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | Configure alerts when a limiter exceeds a defined threshold percentage.
    |
    */
    'notifications' => [
        'enabled' => env('RATE_LIMIT_NOTIFY_ENABLED', false),
        'channels' => ['mail', 'slack'],
        'threshold_percent' => env('RATE_LIMIT_NOTIFY_THRESHOLD', 80),
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard Route Configuration
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the route prefix and middleware
    | applied to the dashboard routes. Make sure to protect these routes
    | using an auth middleware and appropriate authorization gate.
    |
    */
    'dashboard' => [
        'prefix' => 'admin/rate-limits',
        'middleware' => ['web'], // Add 'auth' and 'can:viewRateLimitDashboard' in your app
    ],
];
