<?php

namespace Sa\RateLimitDashboard\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Sa\RateLimitDashboard\Models\RateLimitConfig;
use Sa\RateLimitDashboard\Models\RateLimitEvent;
use Sa\RateLimitDashboard\Models\RateLimitHistorySummary;
use Sa\RateLimitDashboard\Services\HealthChecker;

class ApiController extends Controller
{
    public function metrics(): JsonResponse
    {
        $totalRequests = RateLimitEvent::query()->count();
        $throttledRequests = RateLimitEvent::query()->where('status', 'throttled')->count();

        $limiters = RateLimitEvent::query()
            ->select('limiter_name')
            ->selectRaw('count(*) as total_requests')
            ->selectRaw("sum(case when status = 'throttled' then 1 else 0 end) as throttled_requests")
            ->selectRaw('max(max_attempts) as max_attempts')
            ->selectRaw('max(current_attempts) as current_attempts')
            ->groupBy('limiter_name')
            ->orderByDesc('total_requests')
            ->get()
            ->map(fn (RateLimitEvent $row): array => [
                'name' => (string) $row->getAttribute('limiter_name'),
                'total_requests' => (int) $row->getAttribute('total_requests'),
                'throttled_requests' => (int) $row->getAttribute('throttled_requests'),
                'max_attempts' => (int) $row->getAttribute('max_attempts'),
                'current_attempts' => (int) $row->getAttribute('current_attempts'),
                'utilisation' => (int) $row->getAttribute('max_attempts') > 0
                    ? round((((int) $row->getAttribute('current_attempts') / (int) $row->getAttribute('max_attempts')) * 100), 2)
                    : 0,
            ])
            ->values();

        return response()->json([
            'summary' => [
                'total_requests' => $totalRequests,
                'throttled_requests' => $throttledRequests,
                'limiters_count' => $limiters->count(),
            ],
            'limiters' => $limiters,
        ]);
    }

    public function offenders(): JsonResponse
    {
        return response()->json([
            'offenders' => collect()
                ->merge($this->offendersFor('ip_address', 'ip'))
                ->merge($this->offendersFor('user_id', 'user'))
                ->merge($this->offendersFor('api_token', 'api_token'))
                ->sortByDesc('throttled_count')
                ->values()
                ->take(25)
                ->all(),
        ]);
    }

    public function configs(): JsonResponse
    {
        return response()->json([
            'configs' => RateLimitConfig::query()
                ->orderBy('limiter_name')
                ->get(),
        ]);
    }

    public function summaries(): JsonResponse
    {
        return response()->json([
            'summaries' => RateLimitHistorySummary::query()
                ->orderByDesc('window_start')
                ->limit(100)
                ->get(),
        ]);
    }

    public function checks(HealthChecker $checker): JsonResponse
    {
        return response()->json([
            'checks' => $checker->results(),
        ]);
    }

    protected function offendersFor(string $column, string $type): Collection
    {
        return RateLimitEvent::query()
            ->where('status', 'throttled')
            ->whereNotNull($column)
            ->select($column)
            ->selectRaw('count(*) as throttled_count')
            ->selectRaw('max(created_at) as last_seen')
            ->groupBy($column)
            ->orderByDesc('throttled_count')
            ->limit(10)
            ->get()
            ->map(fn (RateLimitEvent $row): array => [
                'identifier' => (string) $row->getAttribute($column),
                'type' => $type,
                'throttled_count' => (int) $row->getAttribute('throttled_count'),
                'last_seen' => $row->getAttribute('last_seen'),
            ]);
    }
}
