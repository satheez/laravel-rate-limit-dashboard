<?php

namespace Sa\RateLimitDashboard\Services;

use Illuminate\Support\Facades\Route;
use Sa\RateLimitDashboard\Contracts\CheckContract;
use Sa\RateLimitDashboard\Http\Middleware\AuthorizeDashboard;
use Sa\RateLimitDashboard\Models\RateLimitConfig;
use Sa\RateLimitDashboard\Models\RateLimitEvent;
use Sa\RateLimitDashboard\Support\CheckResult;

class HealthChecker
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function results(): array
    {
        $disabled = collect(config('rate-limit-dashboard.disabled_checks', []))
            ->map(fn ($code): string => (string) $code)
            ->all();

        return collect($this->builtInResults())
            ->merge($this->customResults())
            ->reject(fn (CheckResult $result): bool => in_array($result->code, $disabled, true))
            ->values()
            ->map(fn (CheckResult $result): array => $result->toArray())
            ->all();
    }

    /**
     * @return array<int, CheckResult>
     */
    protected function builtInResults(): array
    {
        $results = [
            $this->dashboardProtection(),
            $this->missingStorage(),
            $this->notificationDisabled(),
        ];

        foreach (RateLimitConfig::query()->get() as $config) {
            if ($config->decay_seconds <= 0) {
                $results[] = new CheckResult(
                    'zero_decay',
                    'error',
                    "Limiter {$config->limiter_name} has a non-positive decay window.",
                    'Set decay_seconds to a positive integer.'
                );
            }

            if ($config->decay_seconds > (int) config('rate-limit-dashboard.thresholds.long_decay_seconds', 3600)) {
                $results[] = new CheckResult(
                    'long_decay',
                    'info',
                    "Limiter {$config->limiter_name} has a long decay window.",
                    'Consider using stacked short and long limits.'
                );
            }

            if ($config->max_attempts > (int) config('rate-limit-dashboard.thresholds.huge_max_attempts', 1000)) {
                $results[] = new CheckResult(
                    'huge_max_attempts',
                    'info',
                    "Limiter {$config->limiter_name} allows unusually high request volume.",
                    'Re-check whether the route needs a tighter limit.'
                );
            }
        }

        if (RateLimitEvent::query()->where('status', 'throttled')->exists()) {
            $results[] = new CheckResult(
                'exceeded_limit',
                'error',
                'At least one limiter has returned throttled responses.',
                'Review top offenders and tune the affected limiter.'
            );
        }

        if ($this->hasRapidOffender()) {
            $results[] = new CheckResult(
                'rapid_offenders',
                'critical',
                'A client has triggered repeated throttles in the last minute.',
                'Investigate or block the offending client.'
            );
        }

        if ($this->hasHighUtilisation()) {
            $results[] = new CheckResult(
                'high_utilisation',
                'warning',
                'A limiter is at or above the configured utilisation threshold.',
                'Review whether the traffic is legitimate.'
            );
        }

        if ($this->hasUnconfiguredRoutes()) {
            $results[] = new CheckResult(
                'unconfigured_routes',
                'warning',
                'One or more application routes do not use throttle middleware.',
                'Add throttle middleware to public routes that need protection.'
            );
        }

        return $results;
    }

    protected function dashboardProtection(): CheckResult
    {
        $middleware = config('rate-limit-dashboard.dashboard.middleware', []);
        $protected = in_array(AuthorizeDashboard::class, $middleware, true)
            || collect($middleware)->contains(fn ($item): bool => is_string($item) && str_starts_with($item, 'auth'));

        return new CheckResult(
            'dashboard_unprotected',
            $protected ? 'info' : 'critical',
            $protected ? 'Dashboard routes include access-control middleware.' : 'Dashboard routes are missing access-control middleware.',
            $protected ? null : 'Add auth or AuthorizeDashboard middleware.'
        );
    }

    protected function missingStorage(): CheckResult
    {
        $hasEvents = RateLimitEvent::query()->exists();

        return new CheckResult(
            'missing_storage',
            $hasEvents ? 'info' : 'error',
            $hasEvents ? 'Rate-limit storage is receiving events.' : 'No rate-limit events have been recorded yet.',
            $hasEvents ? null : 'Verify middleware registration and database connectivity.'
        );
    }

    protected function notificationDisabled(): CheckResult
    {
        $enabled = (bool) config('rate-limit-dashboard.notifications.enabled', false);

        return new CheckResult(
            'notification_disabled',
            $enabled ? 'info' : 'info',
            $enabled ? 'Notifications are enabled.' : 'Notifications are disabled.',
            $enabled ? null : 'Enable notifications if threshold alerts are required.'
        );
    }

    protected function hasRapidOffender(): bool
    {
        $threshold = (int) config('rate-limit-dashboard.notifications.rapid_offender_threshold', 10);

        return RateLimitEvent::query()
            ->where('status', 'throttled')
            ->where('created_at', '>=', now()->subMinute())
            ->selectRaw('coalesce(ip_address, user_id, api_token, limiter_key) as offender, count(*) as aggregate')
            ->groupBy('offender')
            ->having('aggregate', '>=', $threshold)
            ->exists();
    }

    protected function hasHighUtilisation(): bool
    {
        $threshold = (int) config('rate-limit-dashboard.thresholds.high_utilisation_percent', 80);

        return RateLimitEvent::query()
            ->get()
            ->contains(fn (RateLimitEvent $event): bool => $event->max_attempts > 0
                && (($event->current_attempts / $event->max_attempts) * 100) >= $threshold);
    }

    protected function hasUnconfiguredRoutes(): bool
    {
        foreach (Route::getRoutes()->getRoutes() as $route) {
            $uri = $route->uri();

            if (str_starts_with($uri, trim((string) config('rate-limit-dashboard.dashboard.prefix'), '/'))) {
                continue;
            }

            if (! collect($route->gatherMiddleware())->contains(fn ($middleware): bool => is_string($middleware)
                && str_contains($middleware, 'throttle'))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, CheckResult>
     */
    protected function customResults(): array
    {
        return collect(config('rate-limit-dashboard.checks', []))
            ->map(fn (string $check): CheckContract => app($check))
            ->map(fn (CheckContract $check): CheckResult => $check->run())
            ->all();
    }
}
