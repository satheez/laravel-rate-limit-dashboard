<?php

namespace Sa\RateLimitDashboard\Listeners;

use Illuminate\Support\Str;
use Sa\RateLimitDashboard\Events\RateLimitThrottled;
use Sa\RateLimitDashboard\Models\RateLimitEvent;

class RecordRateLimitEvent
{
    public function handle($event): void
    {
        $status = $event instanceof RateLimitThrottled ? 'throttled' : 'hit';

        $userId = null;
        if ($event->request->user()) {
            $userId = $event->request->user()->getAuthIdentifier();
        }

        $apiToken = $event->request->bearerToken();
        if ($apiToken) {
            $apiToken = Str::limit(hash('sha256', (string) $apiToken), 16, ''); // Hash and truncate for security
        }

        RateLimitEvent::create([
            'limiter_name' => $event->limiterName,
            'limiter_key' => $event->limiterKey,
            'max_attempts' => $event->maxAttempts,
            'current_attempts' => $event->currentAttempts ?? $event->maxAttempts,
            'request_method' => $event->request->method(),
            'url_path' => $event->request->path(),
            'status' => $status,
            'ip_address' => $event->request->ip(),
            'user_id' => $userId,
            'api_token' => $apiToken,
            'created_at' => now(),
        ]);
    }
}
