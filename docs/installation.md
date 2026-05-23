# Installation

## Requirements

- PHP 8.1 or higher
- Laravel 11.x, 12.x, or 13.x
- A supported database (MySQL, PostgreSQL, MongoDB, or Redis)

## 1. Require the Package

Install via Composer:

```bash
composer require satheez/laravel-rate-limit-dashboard
```

The package will automatically register its service provider.

## 2. Publish Assets

Publish the configuration file and database migrations:

```bash
# Publish Configuration
php artisan vendor:publish --provider="Sa\RateLimitDashboard\RateLimitDashboardServiceProvider" --tag="config"

# Publish Migrations
php artisan vendor:publish --provider="Sa\RateLimitDashboard\RateLimitDashboardServiceProvider" --tag="migrations"
```

*Optional: If you need to override the views, you can publish them:*
```bash
php artisan vendor:publish --provider="Sa\RateLimitDashboard\RateLimitDashboardServiceProvider" --tag="views"
```

## 3. Run Migrations

Create the necessary tables (`rate_limit_events`, `rate_limit_configs`, `rate_limit_history_summaries`):

```bash
php artisan migrate
```

## 4. Register the Middleware

To track rate limits, you must add the instrumenter middleware to your HTTP kernel. Open `bootstrap/app.php` (Laravel 11+) or `app/Http/Kernel.php` (Laravel 10/11) and append it to your web or api middleware groups.

```php
protected $middleware = [
    // ...
    \Sa\RateLimitDashboard\Http\Middleware\RateLimitInstrumenter::class,
];
```

## 5. Configure Authorisation

By default, the dashboard is protected by the `viewRateLimitDashboard` Gate. You must define this gate to allow access. 

Open `app/Providers/AppServiceProvider.php` (or `AuthServiceProvider.php`):

```php
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    Gate::define('viewRateLimitDashboard', function ($user) {
        return in_array($user->email, [
            'admin@yourdomain.com',
        ]);
    });
}
```

## 6. Access the Dashboard

Navigate your browser to `/admin/rate-limits`. You should see the dashboard UI. As traffic hits your rate-limited endpoints, the charts and top offenders list will populate automatically.
