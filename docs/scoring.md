# Scoring & Thresholds

## Utilisation

Utilisation is calculated as `current_attempts / max_attempts * 100`. Health checks use `thresholds.high_utilisation_percent`, defaulting to 80.

## Offenders

The API groups throttled events by:

- IP address
- User ID
- Hashed API token

The highest throttled counts are returned first by `/api/offenders`.

## Alert Thresholds

Saved limiter configs include an `alert_threshold`. When `rate-limit:check-alerts` runs, it checks latest recorded utilisation for each saved limiter and sends a mail notification if the threshold is met.
