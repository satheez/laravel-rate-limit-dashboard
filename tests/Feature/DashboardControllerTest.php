<?php

use Sa\RateLimitDashboard\Models\RateLimitEvent;

it('displays the dashboard with stats and recent events', function (): void {
    // Generate some fake data
    RateLimitEvent::create([
        'limiter_name' => 'test_limiter',
        'limiter_key' => 'test_key',
        'max_attempts' => 60,
        'current_attempts' => 1,
        'request_method' => 'GET',
        'url_path' => '/',
        'status' => 'hit',
        'ip_address' => '192.168.1.100',
        'created_at' => now(),
    ]);

    $response = $this->get('/admin/rate-limits');

    $response->assertOk();
    $response->assertViewIs('rate-limit-dashboard::dashboard');
    $response->assertSee('test_limiter');
    $response->assertSee('192.168.1.100');
});
