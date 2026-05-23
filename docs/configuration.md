# Configuration

Publish the config file via:

```bash
php artisan vendor:publish --provider="Sa\RateLimitDashboard\RateLimitDashboardServiceProvider" --tag="rate-limit-dashboard-config"
```

## Environment Variables

| Variable | Default | Purpose |
|---|---|---|
| `RATE_LIMIT_DASHBOARD_ENABLED` | `true` | Enable event tracking and dashboard routes. |
| `RATE_LIMIT_STORAGE` | `database` | Reserved storage setting. Current implementation stores through Eloquent. |
| `RATE_LIMIT_RETENTION_DAYS` | `30` | Raw event retention for `rate-limit:prune`. |
| `RATE_LIMIT_NOTIFY_ENABLED` | `false` | Enable threshold mail notifications. |
| `RATE_LIMIT_NOTIFY_MAIL_TO` | `null` | Recipient for threshold notifications. |
| `RATE_LIMIT_NOTIFY_THRESHOLD` | `80` | Default alert threshold percentage. |
| `RATE_LIMIT_RAPID_OFFENDER_THRESHOLD` | `10` | Throttles per minute required for the rapid offender check. |

## Dashboard Access

Default dashboard middleware:

```php
'dashboard' => [
    'prefix' => 'admin/rate-limits',
    'middleware' => ['web', \Sa\RateLimitDashboard\Http\Middleware\AuthorizeDashboard::class],
    'authorization_gate' => 'viewRateLimitDashboard',
],
```

`AuthorizeDashboard` denies guests. If the configured gate exists, it must allow the current user.

## Runtime Limiter Config

Saved limiter config is used by `RateLimitInstrumenter` when a route uses a matching limiter name.

```php
'default_limits' => [
    'max_attempts' => env('RATE_LIMIT_DEFAULT_MAX_ATTEMPTS', 60),
    'decay_seconds' => env('RATE_LIMIT_DEFAULT_DECAY', 60),
],
```

Override JSON shape:

```json
{
  "ip": {
    "127.0.0.1": {"max_attempts": 120, "decay_seconds": 60}
  },
  "user": {
    "42": {"max_attempts": 1000, "decay_seconds": 60}
  }
}
```

## Health Checks

Custom checks may implement `Sa\RateLimitDashboard\Contracts\CheckContract` and be registered in `checks`. Disable built-in or custom check codes through `disabled_checks`.
