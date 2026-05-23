<?php

namespace Sa\RateLimitDashboard\Http\Middleware;

use Closure;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\RateLimiter;
use Sa\RateLimitDashboard\Events\RateLimitHit;
use Sa\RateLimitDashboard\Events\RateLimitThrottled;
use Symfony\Component\HttpFoundation\Response;

class RateLimitInstrumenter extends ThrottleRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  int|string  $maxAttempts
     * @param  float|int  $decayMinutes
     * @param  string  $prefix
     * @return Response
     *
     * @throws ThrottleRequestsException
     */
    public function handle($request, Closure $next, $maxAttempts = 60, $decayMinutes = 1, $prefix = '')
    {
        if (! config('rate-limit-dashboard.enabled')) {
            return parent::handle($request, $next, $maxAttempts, $decayMinutes, $prefix);
        }

        $limiterName = $this->getLimiterName($request, $maxAttempts);
        $key = $prefix.$this->resolveRequestSignature($request);
        $maxAttempts = $this->resolveMaxAttempts($request, $maxAttempts);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            event(new RateLimitThrottled($limiterName, $key, $maxAttempts, $request));

            throw $this->buildException($request, $key, $maxAttempts, null);
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        $currentAttempts = RateLimiter::attempts($key);
        event(new RateLimitHit($limiterName, $key, $maxAttempts, $currentAttempts, $request));

        $response = $next($request);

        return $this->addHeaders(
            $response, $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts)
        );
    }

    /**
     * Guess the limiter name based on route or arguments.
     */
    protected function getLimiterName(Request $request, $maxAttempts)
    {
        if (is_string($maxAttempts) && func_num_args() === 3) {
            return $maxAttempts; // Custom limiter name
        }

        if ($route = $request->route()) {
            return $route->getName() ?? $route->uri();
        }

        return 'default';
    }
}
