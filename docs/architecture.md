# Architecture

## Source Layout

```
src/
├── Console/
│   └── PruneEventsCommand.php           # Artisan command to clean up old rate-limit events
├── Http/
│   ├── Controllers/
│   │   ├── DashboardController.php      # Renders the UI
│   │   └── ApiController.php            # JSON API for metrics
│   └── Middleware/
│       └── RateLimitInstrumenter.php    # Wraps the throttle middleware to emit events
├── Listeners/
│   └── RateLimitEventSubscriber.php     # Captures and stores event data
├── Services/
│   ├── MetricsAggregator.php            # Rolls up events into minute/hour summaries
│   ├── ConfigurationManager.php         # Handles dynamic limit overrides
│   └── HealthChecker.php                # Runs built-in health checks
├── Storage/
│   ├── DatabaseStorage.php              # Relational DB implementation
│   └── RedisStorage.php                 # Redis implementation
├── Exceptions/
└── RateLimitDashboardServiceProvider.php
```

## System Layers

### 1. Instrumentation Layer
The `RateLimitInstrumenter` middleware is a lightweight wrapper around Laravel's `ThrottleRequests` middleware. When a request hits a rate limit or exceeds it, the middleware emits a `RateLimitEvent` to Laravel's event bus containing metadata (e.g., Limiter name, Max allowed attempts, IP address, Timestamp).

### 2. Data Processing & Storage Layer
An asynchronous event listener catches these events and stores them in the selected backend (`database` or `redis`). A background aggregator then rolls up these raw events into time-window summaries (e.g., `rate_limit_history_summaries`) to keep dashboard queries fast.

### 3. Presentation Layer
The Livewire/Vue Dashboard UI queries the aggregated summaries to draw charts, list top offenders, and provide form inputs for configuration overrides. This layer is protected by the `viewRateLimitDashboard` Gate.

## Data Model (Relational)

### `rate_limit_events`
Stores raw events.
- `id` (UUID), `limiter_name`, `limiter_key`, `max_attempts`, `current_attempts`, `ip_address`, `user_id`, `status` (hit/throttled), `timestamp`

### `rate_limit_configs`
Stores runtime overrides.
- `limiter_name`, `max_attempts`, `decay_seconds`, `overrides` (JSON for per-IP rules)

### `rate_limit_history_summaries`
Stores aggregated data.
- `limiter_name`, `time_window`, `total_requests`, `throttled_requests`
