# Usage

## Dashboard

Visit the configured dashboard prefix, `/admin/rate-limits` by default. The dashboard includes:

- Summary metrics for total and throttled requests.
- Hourly volume bars from aggregated summaries.
- Health check results.
- Runtime limiter configuration forms.
- Limiter activity, top throttled IPs, and recent events.

## Instrumenting Routes

For package-managed metrics, use the package middleware in place of `throttle`:

```php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Sa\RateLimitDashboard\Http\Middleware\RateLimitInstrumenter;

RateLimiter::for('api', function ($request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});

Route::middleware(RateLimitInstrumenter::class.':api')->group(function () {
    Route::get('/api/search', SearchController::class);
});
```

The middleware supports numeric limits (`RateLimitInstrumenter::class.':60,1'`) and named limiters (`RateLimitInstrumenter::class.':api'`).

## Runtime Overrides

Use the dashboard configuration form to save a limiter name, max attempts, decay seconds, alert threshold, and optional override JSON. A saved config applies when the middleware limiter name matches `limiter_name`.

## API

The dashboard middleware protects API endpoints:

- `GET /admin/rate-limits/api/metrics`
- `GET /admin/rate-limits/api/offenders`
- `GET /admin/rate-limits/api/configs`
- `GET /admin/rate-limits/api/summaries`
- `GET /admin/rate-limits/api/checks`

## Commands

```bash
php artisan rate-limit:prune
php artisan rate-limit:check-alerts
```
