# Output & API

The package provides two main interfaces to consume the rate-limit metrics: the Web Dashboard and the JSON API.

## 1. Web Dashboard

The Livewire/Vue dashboard (located at `/admin/rate-limits` by default) provides real-time visualizations.

### Summary Cards
- **Total Requests**: Number of requests evaluated by limiters in the last 24 hours.
- **Throttled**: Number of `429 Too Many Requests` events in the last 24 hours.
- **Limit Utilisation**: The average percentage of limits used across all active limiters.

### Charts
- **Time Series Line Chart**: Shows requests vs throttled hits over time (binned by minute/hour).
- **Endpoint Distribution Pie Chart**: Shows which limiters/routes are consuming the most traffic.

### Top Offenders List
A data table highlighting the highest number of throttled hits, grouped by:
- IP Address
- User ID (if authenticated)
- API Token (truncated/hashed)

## 2. JSON API

If you wish to build your own dashboard or integrate the metrics into external tools like Grafana, you can use the built-in API.

Enable the API in your configuration if it's not enabled by default, and access the endpoints using the same authentication gate as the dashboard.

### `GET /admin/rate-limits/api/metrics`
Returns aggregated time-series data.

**Response Example:**
```json
{
  "summary": {
    "total_requests": 14500,
    "throttled": 120,
    "time_window": "24h"
  },
  "limiters": [
    {
      "name": "api_global",
      "max_attempts": 60,
      "decay_seconds": 60,
      "utilisation": 45.5,
      "throttled_hits": 15
    }
  ]
}
```

### `GET /admin/rate-limits/api/offenders`
Returns the list of top offenders.

**Response Example:**
```json
{
  "offenders": [
    {
      "identifier": "192.168.1.100",
      "type": "ip",
      "throttled_count": 85,
      "last_seen": "2026-05-23T10:15:00Z"
    },
    {
      "identifier": "User ID 42",
      "type": "user",
      "throttled_count": 35,
      "last_seen": "2026-05-23T09:45:00Z"
    }
  ]
}
```
