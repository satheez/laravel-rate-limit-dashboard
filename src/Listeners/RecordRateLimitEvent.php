<?php

namespace Sa\RateLimitDashboard\Listeners;

use Carbon\CarbonImmutable;
use Sa\RateLimitDashboard\Events\RateLimitThrottled;
use Sa\RateLimitDashboard\Models\RateLimitEvent;
use Sa\RateLimitDashboard\Models\RateLimitHistorySummary;

class RecordRateLimitEvent
{
    public function handle($event): void
    {
        $status = $event instanceof RateLimitThrottled ? 'throttled' : 'hit';

        RateLimitEvent::create([
            'limiter_name' => $event->limiterName,
            'limiter_key' => $event->limiterKey,
            'max_attempts' => $event->maxAttempts,
            'current_attempts' => $event->currentAttempts,
            'request_method' => $event->requestMethod,
            'url_path' => $event->urlPath,
            'status' => $status,
            'ip_address' => $event->ipAddress,
            'user_id' => is_numeric($event->userId) ? (int) $event->userId : null,
            'api_token' => $event->apiToken,
            'created_at' => now(),
        ]);

        $this->recordSummaries($event->limiterName, $status === 'throttled');
    }

    protected function recordSummaries(string $limiterName, bool $throttled): void
    {
        $now = CarbonImmutable::now();
        $windows = [
            'minute' => $now->startOfMinute(),
            'hour' => $now->startOfHour(),
            'day' => $now->startOfDay(),
        ];

        foreach ($windows as $window => $windowStart) {
            $summary = RateLimitHistorySummary::firstOrCreate(
                [
                    'limiter_name' => $limiterName,
                    'time_window' => $window,
                    'window_start' => $windowStart,
                ],
                [
                    'total_requests' => 0,
                    'throttled_requests' => 0,
                ]
            );

            $summary->increment('total_requests');

            if ($throttled) {
                $summary->increment('throttled_requests');
            }
        }
    }
}
