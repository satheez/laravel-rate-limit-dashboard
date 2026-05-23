# Output & API

## Web Dashboard

The Blade dashboard is self-contained and does not require Node, Tailwind CDN, Livewire, or Vue. It renders:

- Total request, throttled request, and active limiter cards.
- Hourly volume bars from `rate_limit_history_summaries`.
- Health check results.
- Runtime limiter configuration forms.
- Limiter activity tables.
- Top throttled IP and recent event tables.

## JSON API

### `GET /admin/rate-limits/api/metrics`

Returns summary counts and per-limiter totals.

### `GET /admin/rate-limits/api/offenders`

Returns top throttled identifiers grouped by IP address, user ID, and hashed API token.

### `GET /admin/rate-limits/api/configs`

Returns saved runtime limiter configuration.

### `GET /admin/rate-limits/api/summaries`

Returns recent aggregate summary windows.

### `GET /admin/rate-limits/api/checks`

Returns health check result objects with `code`, `severity`, `message`, `action`, and `meta`.
