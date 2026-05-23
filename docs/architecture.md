# Architecture

## Source Layout

```text
src/
├── Console/
│   ├── CheckRateLimitAlertsCommand.php
│   └── PruneRateLimitEventsCommand.php
├── Contracts/
│   └── CheckContract.php
├── Events/
│   ├── RateLimitHit.php
│   └── RateLimitThrottled.php
├── Http/
│   ├── Controllers/
│   │   ├── ApiController.php
│   │   └── DashboardController.php
│   └── Middleware/
│       ├── AuthorizeDashboard.php
│       └── RateLimitInstrumenter.php
├── Listeners/
│   └── RecordRateLimitEvent.php
├── Models/
├── Notifications/
├── Services/
│   └── HealthChecker.php
└── Support/
```

## Instrumentation

`RateLimitInstrumenter` can replace Laravel's throttle middleware for routes that should be recorded. It mirrors Laravel throttle behavior for numeric and named limiters, including multiple `Limit` objects, unlimited limiters, custom response callbacks, and package-managed DB overrides.

Registering the middleware without limiter arguments is observer-safe and does not apply a default throttle.

## Event Storage and Aggregation

The middleware dispatches scalar, queue-safe `RateLimitHit` and `RateLimitThrottled` events. `RecordRateLimitEvent` writes raw events to `rate_limit_events` and increments minute, hour, and day rows in `rate_limit_history_summaries`.

## Dashboard and API

Dashboard routes are protected by configurable middleware. The Blade dashboard and JSON API read from Eloquent models and aggregated summaries.

## Runtime Configuration

`rate_limit_configs` stores limiter overrides. Changes made through the dashboard are audited in `rate_limit_config_audits` and cached briefly for request-time lookup.

## Maintenance

- `rate-limit:prune` deletes old raw events according to `retention_days`.
- `rate-limit:check-alerts` sends mail notifications when saved limiter configs exceed their alert threshold.
