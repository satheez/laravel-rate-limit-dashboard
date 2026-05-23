# Health Checks Reference

The package ships with built-in automated checks that analyze your rate-limiting configuration and runtime metrics. These checks raise advisories to help you proactively identify abuse or misconfigurations.

## Built-in Checks

| Code | Severity | Description | Action Required |
|---|---|---|---|
| `unconfigured_routes` | ⚠️ Warning | Found routes without `throttle` middleware or custom limiters. | Apply rate limits to all publicly accessible endpoints, especially those writing data. |
| `high_utilisation` | ⚠️ Warning | Attempts used are ≥ the configured alert threshold (e.g., 80%). | Review `max_attempts` and increase them for critical endpoints if traffic is legitimate. |
| `exceeded_limit` | 🔴 Error | Throttled events > 0 in the current monitoring window. | Investigate the offending IPs; consider adjusting limits or implementing exponential backoff. |
| `top_offenders` | ℹ️ Info | Identifies IPs/Users with the highest number of throttled hits. | Block extreme abusers at the WAF/Firewall level or contact users if it's a misconfigured client. |
| `rapid_offenders` | 🚨 Critical | An IP/user repeatedly hits the 429 limit within a 1-minute window. | Temporarily block the client, enforce a CAPTCHA, or ban the IP. |
| `long_decay` | ℹ️ Info | `decay_seconds` exceeds configured maximum (e.g., > 3600s). | Consider stacking limiters (e.g., 100/min AND 1000/day) instead of one huge window. |
| `zero_decay` | 🔴 Error | `decay_seconds` is `0` or null. | This renders the limiter invalid. Set a proper decay time. |
| `huge_max_attempts` | ℹ️ Info | `max_attempts` exceeds expected upper bound (e.g., > 1000). | Apply separate strict limits for external traffic vs internal webhook traffic. |
| `missing_storage` | 🔴 Error | No events recorded despite high traffic. | Verify the `RateLimitInstrumenter` middleware is registered and storage is reachable. |
| `dashboard_unprotected` | 🚨 Critical | Dashboard route has no `auth` middleware. | Add `auth` and your gate (`can:viewRateLimitDashboard`) to `config/rate-limit-dashboard.php`. |

## Adding Custom Checks

You can add custom domain-specific checks by implementing the `CheckContract`:

```php
use Sa\RateLimitDashboard\Contracts\CheckContract;

class VerifyTenantQuotas implements CheckContract
{
    public function run($config, $metrics): CheckResult
    {
        // Custom logic to verify SaaS tenant limits
    }
}
```

Register it in `config/rate-limit-dashboard.php`:
```php
'checks' => [
    VerifyTenantQuotas::class,
]
```
