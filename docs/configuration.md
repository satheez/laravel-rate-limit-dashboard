# Configuration

Publish the config file via:

```bash
php artisan vendor:publish --provider="Sa\RateLimitDashboard\RateLimitDashboardServiceProvider" --tag="config"
```

This creates `config/rate-limit-dashboard.php`.

## Environment Variables

| Variable | Default | Purpose |
|---|---|---|
| `RATE_LIMIT_DASHBOARD_ENABLED` | `true` | Globally enable/disable event tracking and the dashboard. |
| `RATE_LIMIT_STORAGE` | `database` | Storage driver (`database`, `redis`). |
| `RATE_LIMIT_RETENTION_DAYS` | `30` | How long to keep raw event records before pruning. |
| `RATE_LIMIT_NOTIFY_ENABLED` | `false` | Enable automated alerts. |
| `RATE_LIMIT_NOTIFY_THRESHOLD` | `80` | Alert threshold percentage (e.g., alert when 80% of limit is used). |

## Full Config Reference

```php
return [
    // Enable or disable the entire monitoring package.
    'enabled' => env('RATE_LIMIT_DASHBOARD_ENABLED', true),

    // Storage driver: 'database', 'redis', or custom class implementing RateLimitStorage.
    'storage' => env('RATE_LIMIT_STORAGE', 'database'),

    // Event retention policy in days. Set to null for indefinite retention.
    'retention_days' => env('RATE_LIMIT_RETENTION_DAYS', 30),

    // Default limit values when no custom limiter is defined.
    'default_limits' => [
        'max_attempts' => env('RATE_LIMIT_DEFAULT_MAX_ATTEMPTS', 60),
        'decay_seconds' => env('RATE_LIMIT_DEFAULT_DECAY', 60),
    ],

    // Notification settings for threshold alerts.
    'notifications' => [
        'enabled' => env('RATE_LIMIT_NOTIFY_ENABLED', false),
        'channels' => ['mail', 'slack'], // standard Laravel notification channels
        'threshold_percent' => env('RATE_LIMIT_NOTIFY_THRESHOLD', 80),
    ],

    // Dashboard route configuration.
    'dashboard' => [
        'prefix' => 'admin/rate-limits',
        'middleware' => ['web', 'auth', 'can:viewRateLimitDashboard'],
    ],

    // Array of CheckContract classes to run automatically
    'checks' => [
        // \Sa\RateLimitDashboard\Checks\HighUtilisationCheck::class,
    ],
    
    // Disable specific checks by their string code
    'disabled_checks' => [
        'huge_max_attempts',
    ],
];
```

## Securing the Dashboard

The dashboard is secured by a Laravel Gate. Define this gate in your `App\Providers\AuthServiceProvider` or `App\Providers\AppServiceProvider`:

```php
use Illuminate\Support\Facades\Gate;

public function boot()
{
    Gate::define('viewRateLimitDashboard', function ($user) {
        // Example: Only allow admins
        return $user->isAdmin();
    });
}
```
