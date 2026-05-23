<?php

namespace Sa\RateLimitDashboard\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Sa\RateLimitDashboard\Models\RateLimitConfig;
use Sa\RateLimitDashboard\Models\RateLimitConfigAudit;
use Sa\RateLimitDashboard\Models\RateLimitEvent;
use Sa\RateLimitDashboard\Models\RateLimitHistorySummary;
use Sa\RateLimitDashboard\Services\HealthChecker;

class DashboardController extends Controller
{
    public function index(HealthChecker $checker)
    {
        $stats = [
            'total_requests' => RateLimitEvent::count(),
            'throttled_requests' => RateLimitEvent::where('status', 'throttled')->count(),
            'limiters_count' => RateLimitConfig::count() ?: RateLimitEvent::distinct('limiter_name')->count('limiter_name'),
        ];

        $recentEvents = RateLimitEvent::orderBy('created_at', 'desc')->limit(50)->get();

        $topOffenders = RateLimitEvent::where('status', 'throttled')
            ->select('ip_address', DB::raw('count(*) as count'))
            ->groupBy('ip_address')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        $limiters = RateLimitEvent::query()
            ->select('limiter_name')
            ->selectRaw('count(*) as total_requests')
            ->selectRaw("sum(case when status = 'throttled' then 1 else 0 end) as throttled_requests")
            ->selectRaw('max(max_attempts) as max_attempts')
            ->selectRaw('max(current_attempts) as current_attempts')
            ->groupBy('limiter_name')
            ->orderByDesc('total_requests')
            ->limit(25)
            ->get();

        $summaries = RateLimitHistorySummary::query()
            ->where('time_window', 'hour')
            ->orderBy('window_start')
            ->limit(24)
            ->get();

        return view()->make('rate-limit-dashboard::dashboard', [
            'stats' => $stats,
            'recentEvents' => $recentEvents,
            'topOffenders' => $topOffenders,
            'limiters' => $limiters,
            'configs' => RateLimitConfig::query()->orderBy('limiter_name')->get(),
            'checks' => $checker->results(),
            'summaries' => $summaries,
        ]);
    }

    public function storeConfig(Request $request): RedirectResponse
    {
        $data = $this->validatedConfig($request);
        $existing = RateLimitConfig::query()->find($data['limiter_name']);
        $before = $existing instanceof RateLimitConfig ? $existing->toArray() : null;

        $config = RateLimitConfig::updateOrCreate(
            ['limiter_name' => $data['limiter_name']],
            $data
        );

        $this->recordAudit($request, $config->limiter_name, $before ? 'update' : 'create', $before, $config->toArray());
        Cache::forget('rate-limit-dashboard:config:'.$config->limiter_name);

        return back()->with('status', 'Limiter configuration saved.');
    }

    public function destroyConfig(Request $request, string $limiterName): RedirectResponse
    {
        $config = RateLimitConfig::query()->findOrFail($limiterName);
        $before = $config->toArray();

        $config->delete();
        Cache::forget('rate-limit-dashboard:config:'.$limiterName);

        $this->recordAudit($request, $limiterName, 'delete', $before, null);

        return back()->with('status', 'Limiter configuration deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatedConfig(Request $request): array
    {
        $data = $request->validate([
            'limiter_name' => ['required', 'string', 'max:255'],
            'max_attempts' => ['required', 'integer', 'min:1', 'max:1000000'],
            'decay_seconds' => ['required', 'integer', 'min:1', 'max:604800'],
            'alert_threshold' => ['required', 'integer', 'min:1', 'max:100'],
            'overrides' => ['nullable', 'json'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['overrides'] = filled($data['overrides'] ?? null)
            ? json_decode((string) $data['overrides'], true)
            : null;

        unset($data['reason']);

        return $data;
    }

    /**
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     */
    protected function recordAudit(Request $request, string $limiterName, string $action, ?array $before, ?array $after): void
    {
        $userId = $request->user()?->getAuthIdentifier();

        RateLimitConfigAudit::create([
            'limiter_name' => $limiterName,
            'user_id' => is_numeric($userId) ? (int) $userId : null,
            'action' => $action,
            'before' => $before,
            'after' => $after,
            'reason' => $request->string('reason')->toString(),
        ]);
    }
}
