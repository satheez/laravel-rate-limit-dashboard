# Frequently Asked Questions

### Does this add overhead to requests?

Routes using `RateLimitInstrumenter` dispatch an event and write through the configured event listener. The event payload is scalar and queue-safe, so applications may queue listeners if they need lower request latency.

### Does registering the middleware globally throttle every route?

No. Without limiter arguments the middleware does not apply a default throttle. Use it with arguments, such as `RateLimitInstrumenter::class.':api'`, for routes that should be enforced and recorded.

### Will my database grow indefinitely?

Raw events are pruned with `php artisan rate-limit:prune`, using `retention_days` from the package config. Aggregated summaries are retained until removed by your own data policy.

### What storage drivers are supported?

The current implementation stores through Eloquent, so it supports SQL databases supported by Laravel's database layer. Redis, MongoDB, and custom storage drivers are not implemented yet.

### How are API tokens stored?

Bearer tokens are SHA-256 hashed and truncated before storage. Plain bearer tokens are never written to `rate_limit_events`.

### How do threshold alerts work?

Enable notifications, set `RATE_LIMIT_NOTIFY_MAIL_TO`, save limiter configs with alert thresholds, and schedule `rate-limit:check-alerts`.
