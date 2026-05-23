<?php

use Illuminate\Support\Facades\Notification;
use Sa\RateLimitDashboard\Models\RateLimitConfig;
use Sa\RateLimitDashboard\Models\RateLimitEvent;
use Sa\RateLimitDashboard\Notifications\RateLimitThresholdReached;

it('sends threshold notifications to the configured mail recipient', function (): void {
    Notification::fake();

    config()->set('rate-limit-dashboard.notifications.enabled', true);
    config()->set('rate-limit-dashboard.notifications.channels', ['mail']);
    config()->set('rate-limit-dashboard.notifications.mail_to', 'ops@example.com');

    RateLimitConfig::create([
        'limiter_name' => 'search',
        'max_attempts' => 10,
        'decay_seconds' => 60,
        'alert_threshold' => 50,
    ]);

    RateLimitEvent::create([
        'limiter_name' => 'search',
        'limiter_key' => 'search-key',
        'max_attempts' => 10,
        'current_attempts' => 8,
        'request_method' => 'GET',
        'url_path' => 'search',
        'status' => 'hit',
        'ip_address' => '127.0.0.1',
        'created_at' => now(),
    ]);

    $this->artisan('rate-limit:check-alerts')
        ->assertExitCode(0);

    Notification::assertSentOnDemand(RateLimitThresholdReached::class);
});
