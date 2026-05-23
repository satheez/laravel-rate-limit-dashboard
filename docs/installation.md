# Installation

## Requirements

- PHP 8.2 or higher
- Laravel 11.x, 12.x, or 13.x
- A SQL database supported by Laravel's database layer

## 1. Require the Package

```bash
composer require satheez/laravel-rate-limit-dashboard
```

The package auto-registers its service provider through Laravel package discovery.

## 2. Publish Configuration and Migrations

```bash
php artisan vendor:publish --provider="Sa\RateLimitDashboard\RateLimitDashboardServiceProvider" --tag="rate-limit-dashboard-config"
php artisan vendor:publish --provider="Sa\RateLimitDashboard\RateLimitDashboardServiceProvider" --tag="rate-limit-dashboard-migrations"
```

Optional view publishing:

```bash
php artisan vendor:publish --provider="Sa\RateLimitDashboard\RateLimitDashboardServiceProvider" --tag="rate-limit-dashboard-views"
```

## 3. Run Migrations

```bash
php artisan migrate
```

This creates tables for raw events, runtime limiter config, summary windows, and config audit entries.

## 4. Protect the Dashboard

The default dashboard middleware requires an authenticated user and checks the `viewRateLimitDashboard` gate when it exists.

```php
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    Gate::define('viewRateLimitDashboard', function ($user) {
        return $user->isAdmin();
    });
}
```

## 5. Instrument Routes

Use `RateLimitInstrumenter` instead of Laravel's throttle middleware on routes you want to record:

```php
use Sa\RateLimitDashboard\Http\Middleware\RateLimitInstrumenter;

Route::middleware(RateLimitInstrumenter::class.':api')->group(function () {
    Route::get('/api/search', SearchController::class);
});
```

Named Laravel limiters, multiple `Limit` objects, custom responses, and package-managed DB overrides are supported.

## 6. Maintenance Commands

Schedule pruning and optional threshold checks:

```php
$schedule->command('rate-limit:prune')->daily();
$schedule->command('rate-limit:check-alerts')->everyFiveMinutes();
```
