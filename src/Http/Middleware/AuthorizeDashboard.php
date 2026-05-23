<?php

namespace Sa\RateLimitDashboard\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeDashboard
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_if(is_null($user), 403);

        $gate = config('rate-limit-dashboard.dashboard.authorization_gate');

        if (filled($gate) && Gate::has((string) $gate)) {
            abort_unless(Gate::allows((string) $gate), 403);
        }

        return $next($request);
    }
}
