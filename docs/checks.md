# Health Checks Reference

The package includes built-in checks that inspect configuration and runtime data. Results are shown in the dashboard and exposed through `GET /admin/rate-limits/api/checks`.

| Code | Severity | Description |
|---|---|---|
| `dashboard_unprotected` | info/critical | Verifies dashboard routes include access-control middleware. |
| `missing_storage` | info/error | Reports whether rate-limit events are being stored. |
| `notification_disabled` | info | Reports notification configuration state. |
| `zero_decay` | error | Detects saved limiter configs with non-positive decay. |
| `long_decay` | info | Flags saved limiter configs with long decay windows. |
| `huge_max_attempts` | info | Flags saved limiter configs with unusually high request allowances. |
| `exceeded_limit` | error | Reports whether throttled events exist. |
| `rapid_offenders` | critical | Detects repeated throttles by one identifier in the last minute. |
| `high_utilisation` | warning | Detects events at or above the configured utilisation threshold. |
| `unconfigured_routes` | warning | Finds application routes without throttle middleware. |

## Custom Checks

Implement `Sa\RateLimitDashboard\Contracts\CheckContract` and register the class in `config/rate-limit-dashboard.php`.

```php
'checks' => [
    App\Support\RateLimitChecks\VerifyTenantQuotas::class,
],
```

Disable checks by code:

```php
'disabled_checks' => [
    'notification_disabled',
],
```
