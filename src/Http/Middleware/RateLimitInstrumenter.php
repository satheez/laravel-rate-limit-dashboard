<?php

namespace Sa\RateLimitDashboard\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiting\Unlimited;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Sa\RateLimitDashboard\Events\RateLimitHit;
use Sa\RateLimitDashboard\Events\RateLimitThrottled;
use Sa\RateLimitDashboard\Models\RateLimitConfig;
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
    public function handle($request, Closure $next, $maxAttempts = null, $decayMinutes = null, $prefix = '')
    {
        $explicitLimiter = func_num_args() > 2;

        if (! config('rate-limit-dashboard.enabled')) {
            if (! $explicitLimiter) {
                return $next($request);
            }

            return parent::handle($request, $next, $maxAttempts ?? 60, $decayMinutes ?? 1, $prefix);
        }

        if (! $explicitLimiter) {
            return $next($request);
        }

        if (is_string($maxAttempts)
            && func_num_args() === 3
            && ! is_null($limiter = $this->limiter->limiter($maxAttempts))) {
            return $this->handleRequestUsingNamedLimiter($request, $next, $maxAttempts, $limiter);
        }

        $limiterName = $this->getLimiterName($request, $maxAttempts ?? 60);
        $decaySeconds = 60 * ($decayMinutes ?? 1);

        return $this->handleInstrumentedRequest(
            $request,
            $next,
            $this->applyConfiguredOverrides($request, $limiterName, [
                (object) [
                    'limiterName' => $limiterName,
                    'key' => $prefix.$this->resolveRequestSignature($request),
                    'maxAttempts' => $this->resolveMaxAttempts($request, $maxAttempts ?? 60),
                    'decaySeconds' => $decaySeconds,
                    'responseCallback' => null,
                ],
            ])
        );
    }

    /**
     * Handle a named Laravel limiter while preserving Laravel's throttle behavior.
     */
    protected function handleRequestUsingNamedLimiter($request, Closure $next, $limiterName, Closure $limiter)
    {
        $limiterResponse = $limiter($request);

        if ($limiterResponse instanceof Response) {
            return $limiterResponse;
        }

        if ($limiterResponse instanceof Unlimited) {
            return $next($request);
        }

        $limits = Collection::wrap($limiterResponse)->map(fn ($limit) => (object) [
            'limiterName' => $limiterName,
            'key' => self::$shouldHashKeys
                ? md5($limiterName.$limit->key)
                : $limiterName.':'.$limit->key,
            'maxAttempts' => $limit->maxAttempts,
            'decaySeconds' => $limit->decaySeconds,
            'responseCallback' => $limit->responseCallback,
        ])->all();

        return $this->handleInstrumentedRequest(
            $request,
            $next,
            $this->applyConfiguredOverrides($request, $limiterName, $limits)
        );
    }

    /**
     * Guess the limiter name based on route or arguments.
     */
    protected function getLimiterName(Request $request, $maxAttempts): string
    {
        if (is_string($maxAttempts) && func_num_args() === 3) {
            return $maxAttempts; // Custom limiter name
        }

        if ($route = $request->route()) {
            return $route->getName() ?? $route->uri();
        }

        return 'default';
    }

    /**
     * @param  array<int, object>  $limits
     */
    protected function handleInstrumentedRequest(Request $request, Closure $next, array $limits): Response
    {
        foreach ($limits as $limit) {
            if ($this->limiter->tooManyAttempts($limit->key, $limit->maxAttempts)) {
                event(RateLimitThrottled::fromRequest(
                    $limit->limiterName,
                    $limit->key,
                    $limit->maxAttempts,
                    max($this->limiter->attempts($limit->key), $limit->maxAttempts),
                    $request
                ));

                throw $this->buildException($request, $limit->key, $limit->maxAttempts, $limit->responseCallback);
            }

            $this->limiter->hit($limit->key, $limit->decaySeconds);

            event(RateLimitHit::fromRequest(
                $limit->limiterName,
                $limit->key,
                $limit->maxAttempts,
                $this->limiter->attempts($limit->key),
                $request
            ));
        }

        $response = $next($request);

        foreach ($limits as $limit) {
            $response = $this->addHeaders(
                $response,
                $limit->maxAttempts,
                $this->calculateRemainingAttempts($limit->key, $limit->maxAttempts)
            );
        }

        return $response;
    }

    /**
     * @param  array<int, object>  $limits
     * @return array<int, object>
     */
    protected function applyConfiguredOverrides(Request $request, string $limiterName, array $limits): array
    {
        $config = Cache::remember(
            'rate-limit-dashboard:config:'.$limiterName,
            60,
            fn (): ?RateLimitConfig => RateLimitConfig::query()->find($limiterName)
        );

        if (! $config instanceof RateLimitConfig) {
            return $limits;
        }

        $override = $this->matchingOverride($request, $config->overrides ?? []);

        foreach ($limits as $limit) {
            $limit->maxAttempts = (int) ($override['max_attempts'] ?? $config->max_attempts);
            $limit->decaySeconds = (int) ($override['decay_seconds'] ?? $config->decay_seconds);
        }

        return $limits;
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function matchingOverride(Request $request, array $overrides): array
    {
        $ip = $request->ip();
        $userId = $request->user()?->getAuthIdentifier();

        if ($ip && isset($overrides['ip'][$ip]) && is_array($overrides['ip'][$ip])) {
            return $overrides['ip'][$ip];
        }

        if ($userId && isset($overrides['user'][(string) $userId]) && is_array($overrides['user'][(string) $userId])) {
            return $overrides['user'][(string) $userId];
        }

        return [];
    }
}
