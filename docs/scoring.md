# Scoring & Thresholds

To provide actionable insights, the dashboard evaluates rate-limit events and "scores" offenders and limiters based on risk thresholds.

## Limiter Risk Scoring

Each configured rate limiter receives a status based on its current utilisation (current attempts / max attempts).

| Status | Threshold | Meaning |
|---|---|---|
| **Healthy** | `< 60%` | Usage is well within bounds. |
| **Warning** | `60% - 80%` | Usage is approaching the limit. |
| **Critical** | `> 80%` | Usage is critically high. If notifications are enabled, an alert is triggered. |

If a limiter is consistently in the Critical state, you should consider increasing `max_attempts` (if the traffic is legitimate) or applying strict overrides to the top offenders causing the spike.

## Offender Scoring

The dashboard aggregates throttled hits per IP address, User ID, or API token. We calculate a "Risk Score" for these identifiers to bubble the most aggressive abusers to the top of the "Top Offenders" list.

The Risk Score takes into account:
1. **Volume**: Total number of `429 Throttled` events in the last 24 hours.
2. **Velocity**: The number of throttled events occurring within a concentrated time window (e.g., 50 throttled hits in 1 minute indicates high velocity).

### Example Categorization

- **Bot / DDoS Suspicion**: Extremely high volume and velocity. The dashboard will flag these with a `🚨 Rapid Offender` alert.
- **Misconfigured Client**: Moderate volume but spread evenly over time. Indicates a background script that doesn't respect `Retry-After` headers.
- **Accidental Hit**: 1 or 2 throttled hits. Disregarded in scoring.

## Threshold Alerts

You can configure exactly when you want to be alerted. In `config/rate-limit-dashboard.php`:

```php
'notifications' => [
    'enabled' => env('RATE_LIMIT_NOTIFY_ENABLED', true),
    'channels' => ['slack', 'mail'],
    'threshold_percent' => 80, // Alert when any limiter reaches 80% utilisation
],
```

When a limiter crosses the `threshold_percent`, a notification is dispatched containing:
- The Limiter Name
- Current Utilisation %
- The top 3 IPs/Users contributing to the traffic
