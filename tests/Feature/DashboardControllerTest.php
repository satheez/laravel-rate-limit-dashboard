<?php

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Gate;
use Sa\RateLimitDashboard\Models\RateLimitEvent;

function dashboardUser(): Authenticatable
{
    $user = new class extends Authenticatable
    {
        protected $guarded = [];
    };

    $user->id = 1;
    $user->exists = true;

    return $user;
}

it('rejects unauthenticated dashboard access by default', function (): void {
    $this->get('/admin/rate-limits')->assertForbidden();
});

it('displays the dashboard with stats and recent events', function (): void {
    Gate::define('viewRateLimitDashboard', fn ($user): bool => true);

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

    $response = $this->actingAs(dashboardUser())->get('/admin/rate-limits');

    $response->assertOk();
    $response->assertViewIs('rate-limit-dashboard::dashboard');
    $response->assertSee('test_limiter');
    $response->assertSee('192.168.1.100');
});

it('returns metrics and offender data from json api endpoints', function (): void {
    Gate::define('viewRateLimitDashboard', fn ($user): bool => true);

    RateLimitEvent::create([
        'limiter_name' => 'api_limiter',
        'limiter_key' => 'api_key',
        'max_attempts' => 10,
        'current_attempts' => 10,
        'request_method' => 'GET',
        'url_path' => 'api/search',
        'status' => 'throttled',
        'ip_address' => '10.0.0.1',
        'created_at' => now(),
    ]);

    $this->actingAs(dashboardUser())
        ->getJson('/admin/rate-limits/api/metrics')
        ->assertOk()
        ->assertJsonPath('summary.total_requests', 1)
        ->assertJsonPath('summary.throttled_requests', 1)
        ->assertJsonPath('limiters.0.name', 'api_limiter');

    $this->actingAs(dashboardUser())
        ->getJson('/admin/rate-limits/api/offenders')
        ->assertOk()
        ->assertJsonPath('offenders.0.identifier', '10.0.0.1')
        ->assertJsonPath('offenders.0.type', 'ip');
});

it('returns dashboard checks and respects disabled checks', function (): void {
    Gate::define('viewRateLimitDashboard', fn ($user): bool => true);
    config()->set('rate-limit-dashboard.disabled_checks', ['notification_disabled']);

    $this->actingAs(dashboardUser())
        ->getJson('/admin/rate-limits/api/checks')
        ->assertOk()
        ->assertJsonMissing(['code' => 'notification_disabled'])
        ->assertJsonFragment(['code' => 'missing_storage']);
});
