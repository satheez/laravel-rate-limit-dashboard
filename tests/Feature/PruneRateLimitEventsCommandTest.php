<?php

use Sa\RateLimitDashboard\Models\RateLimitEvent;

it('prunes raw events older than configured retention days', function (): void {
    config()->set('rate-limit-dashboard.retention_days', 30);

    RateLimitEvent::create([
        'limiter_name' => 'old_limiter',
        'limiter_key' => 'old_key',
        'max_attempts' => 60,
        'current_attempts' => 1,
        'request_method' => 'GET',
        'url_path' => 'old',
        'status' => 'hit',
        'ip_address' => '127.0.0.1',
        'created_at' => now()->subDays(31),
    ]);

    RateLimitEvent::create([
        'limiter_name' => 'new_limiter',
        'limiter_key' => 'new_key',
        'max_attempts' => 60,
        'current_attempts' => 1,
        'request_method' => 'GET',
        'url_path' => 'new',
        'status' => 'hit',
        'ip_address' => '127.0.0.1',
        'created_at' => now()->subDays(2),
    ]);

    $this->artisan('rate-limit:prune')
        ->assertExitCode(0);

    expect(RateLimitEvent::pluck('limiter_name')->all())->toBe(['new_limiter']);
});
