<?php

namespace Sa\RateLimitDashboard\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Sa\RateLimitDashboard\Models\RateLimitConfig;
use Sa\RateLimitDashboard\Models\RateLimitEvent;

class DashboardController extends Controller
{
    public function index()
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

        return view('rate-limit-dashboard::dashboard', ['stats' => $stats, 'recentEvents' => $recentEvents, 'topOffenders' => $topOffenders]);
    }
}
