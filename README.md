<div align="center">

# Laravel Rate-Limit Dashboard

**Visibility and operational controls for Laravel rate-limiting.**

![Laravel Rate-Limit Dashboard](docs/assets/banner.png)

[![Tests](https://github.com/satheez/laravel-rate-limit-dashboard/actions/workflows/tests.yml/badge.svg)](https://github.com/satheez/laravel-rate-limit-dashboard/actions/workflows/tests.yml)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-blue)](https://www.php.net)
[![Laravel](https://img.shields.io/badge/Laravel-11%20%7C%2012%20%7C%2013-red)](https://laravel.com)
[![License](https://img.shields.io/packagist/l/satheez/laravel-rate-limit-dashboard.svg)](LICENSE.md)

</div>

---

Laravel's built-in `RateLimiter` facade and throttle middleware allow you to define rate limits, but they provide **no visual interface** to monitor usage or adjust limits in production.

Laravel Rate-Limit Dashboard bridges this gap with instrumentation, persisted metrics, a secured dashboard, runtime limiter configuration, health checks, JSON endpoints, retention pruning, and threshold mail alerts.

> **Who is hitting the rate limits? Which endpoints are being abused? Can we adjust limits without redeploying?**

---

## The Problem

When users encounter HTTP `429 (Too Many Requests)` errors, developers traditionally have no built-in way to:

- See the offending IP or API token
- Identify exactly which endpoint is being hammered
- Adjust the rate limits gracefully without redeploying code

This leads to support tickets, unchecked abuse, and misconfigured limits.

---

## Features

**Real-Time Visibility**

- Dashboard showing total requests, throttled requests, utilisation, hourly volume, health checks, limiter activity, and recent events
- Top offenders grouped by IP, user ID, or API token through the JSON API

**Dynamic Configuration**

- Save package-managed limiter settings from the UI
- Apply per-user and per-IP overrides when using the package instrumenter middleware

**Alerts, Checks, and Maintenance**

- Built-in health checks for storage, dashboard protection, utilisation, decay settings, offenders, and unconfigured routes
- `rate-limit:check-alerts` mail notifications for configured threshold breaches
- `rate-limit:prune` retention cleanup for old raw events

**Storage**

- Uses the host application's configured SQL database through Eloquent
- Stores raw events, runtime limiter configuration, audit entries, and minute/hour/day summaries

---

## Installation

```bash
composer require satheez/laravel-rate-limit-dashboard
```

Publish configuration and migrations:

```bash
php artisan vendor:publish --provider="Sa\RateLimitDashboard\RateLimitDashboardServiceProvider" --tag="rate-limit-dashboard-config"
php artisan vendor:publish --provider="Sa\RateLimitDashboard\RateLimitDashboardServiceProvider" --tag="rate-limit-dashboard-migrations"
php artisan migrate
```

---

## Quick Start

Use the package middleware in place of Laravel's throttle middleware for routes you want to enforce and record:

```php
Route::middleware(\Sa\RateLimitDashboard\Http\Middleware\RateLimitInstrumenter::class.':api')
    ->get('/api/search', SearchController::class);
```

Navigate to the dashboard route (default: `/admin/rate-limits`) after defining the `viewRateLimitDashboard` gate or using your own dashboard middleware.

---

## Documentation

| Document                                | Description                                           |
| --------------------------------------- | ----------------------------------------------------- |
| [Installation](docs/installation.md)    | Requirements, setup, and migrations                   |
| [Usage](docs/usage.md)                  | Dashboard usage and programmatic access               |
| [Configuration](docs/configuration.md)  | Full `config/rate-limit-dashboard.php` reference      |
| [Checks Reference](docs/checks.md)      | Built-in health checks and alert severities           |
| [Scoring & Thresholds](docs/scoring.md) | How offenders are ranked and scored                   |
| [Output & UI](docs/output.md)           | Dashboard interface details and JSON API responses    |
| [Architecture](docs/architecture.md)    | System design, instrumentation layer, data processing |
| [Comparison](docs/comparison.md)        | How this compares to Laravel Pulse, Telescope, etc.   |
| [FAQ](docs/faq.md)                      | Common questions regarding performance and setup      |

---

## Security

See [SECURITY.md](SECURITY.md) for the vulnerability reporting policy.

## License

MIT — see [LICENSE.md](LICENSE.md).
