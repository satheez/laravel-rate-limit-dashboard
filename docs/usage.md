# Usage

Once installed, the dashboard begins tracking rate limit usage instantly for any route protected by the `throttle` middleware or any custom limiters you've defined.

## Accessing the Dashboard

Navigate to your application's domain followed by the configured dashboard prefix (default: `/admin/rate-limits`).

```
https://your-app.test/admin/rate-limits
```

## Dashboard Features

### 1. Global Metrics
The top of the dashboard displays high-level statistics for the current day:
- Total hits across all tracked routes
- Total throttled requests (429s)
- Overall limit utilisation percentage

### 2. Live Limiter Configuration
You can define or edit custom limiters directly from the UI, bypassing the need to define them in your `AppServiceProvider` or routes file.

**To add a dynamic limiter:**
1. Click **"Add Limiter"**
2. Enter an identifier (e.g., `api_global` or a specific route name)
3. Set the **Max Attempts** (e.g., `60`)
4. Set the **Decay Seconds** (e.g., `60`)
5. Click **Save**. The configuration is persisted to the database and immediately takes effect.

### 3. Per-IP or Per-User Overrides
If you have a known good actor (e.g., an internal service) that needs higher limits, or a bad actor that needs strict throttling:

1. Click **"Manage Overrides"** next to a limiter.
2. Select the type (IP or User ID).
3. Enter the identifier (e.g., `192.168.1.150`).
4. Set their custom **Max Attempts** (e.g., `1000` for good actor, `5` for bad actor).
5. Save.

### 4. Viewing Top Offenders
The dashboard includes a table of the top offenders. You can click on any IP address or User ID in this list to instantly jump to the override screen and apply a custom limit restriction against them.

## Programmatic Custom Limiters

If you prefer to keep your configuration in code rather than the database, you can define limiters using Laravel's standard facade:

```php
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;

RateLimiter::for('api', function ($request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});
```

As long as the route uses `middleware('throttle:api')`, the Dashboard's `RateLimitInstrumenter` middleware will capture and log the events, allowing you to see the metrics in the UI, even if you don't use the UI's dynamic configuration feature.
