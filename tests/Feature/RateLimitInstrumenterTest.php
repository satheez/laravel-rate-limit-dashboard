<?php

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Sa\RateLimitDashboard\Events\RateLimitHit;
use Sa\RateLimitDashboard\Events\RateLimitThrottled;
use Sa\RateLimitDashboard\Http\Middleware\RateLimitInstrumenter;
use Sa\RateLimitDashboard\Models\RateLimitConfig;

beforeEach(function (): void {
    config()->set('rate-limit-dashboard.enabled', true);
});

it('does not enforce a default throttle when registered as observer middleware', function (): void {
    Event::fake();

    Route::middleware(RateLimitInstrumenter::class)->get('/observer-only', fn (): string => 'ok')->name('observer.only');

    foreach (range(1, 65) as $attempt) {
        $this->get('/observer-only')->assertOk();
    }

    Event::assertNotDispatched(RateLimitHit::class);
    Event::assertNotDispatched(RateLimitThrottled::class);
});

it('dispatches RateLimitHit event when not throttled', function (): void {
    Event::fake();

    Route::middleware(RateLimitInstrumenter::class.':60,1')->get('/test-route', fn (): string => 'ok')->name('test.route');

    $response = $this->get('/test-route');
    $response->assertOk();

    Event::assertDispatched(RateLimitHit::class, fn ($event): bool => $event->limiterName === 'test.route' &&
           $event->maxAttempts === 60);
    Event::assertNotDispatched(RateLimitThrottled::class);
});

it('dispatches RateLimitThrottled event when throttled', function (): void {
    Event::fake();

    // 1 attempt per minute
    Route::middleware(RateLimitInstrumenter::class.':1,1')->get('/test-throttle', fn (): string => 'ok')->name('test.throttle');

    // First attempt
    $response = $this->get('/test-throttle');
    $response->assertOk();
    Event::assertDispatched(RateLimitHit::class);

    // Second attempt should be throttled
    $response2 = $this->get('/test-throttle');
    $response2->assertStatus(429);

    Event::assertDispatched(RateLimitThrottled::class, fn ($event): bool => $event->limiterName === 'test.throttle' &&
           (int) $event->maxAttempts === 1);
});

it('supports named limiters with multiple limits', function (): void {
    Event::fake();

    RateLimiter::for('multi', fn ($request): array => [
        Limit::perMinute(3)->by($request->ip()),
        Limit::perMinute(1)->by('shared-key'),
    ]);

    Route::middleware(RateLimitInstrumenter::class.':multi')->get('/named-multi', fn (): string => 'ok')->name('named.multi');

    $this->get('/named-multi')->assertOk();
    $this->get('/named-multi')->assertStatus(429);

    Event::assertDispatched(RateLimitHit::class, fn (RateLimitHit $event): bool => $event->limiterName === 'multi' &&
        $event->maxAttempts === 3);
    Event::assertDispatched(RateLimitHit::class, fn (RateLimitHit $event): bool => $event->limiterName === 'multi' &&
        $event->maxAttempts === 1);
    Event::assertDispatched(RateLimitThrottled::class, fn (RateLimitThrottled $event): bool => $event->limiterName === 'multi' &&
        $event->maxAttempts === 1);
});

it('preserves custom named limiter throttle responses', function (): void {
    Event::fake();

    RateLimiter::for('custom-response', fn (): Limit => Limit::perMinute(1)
        ->by('custom-response-key')
        ->response(fn ($request, array $headers): ResponseFactory|\Illuminate\Http\Response => response('custom throttle', 418, $headers)));

    Route::middleware(RateLimitInstrumenter::class.':custom-response')->get('/named-custom-response', fn (): string => 'ok');

    $this->get('/named-custom-response')->assertOk();
    $this->get('/named-custom-response')->assertStatus(418)->assertSee('custom throttle');

    Event::assertDispatched(RateLimitThrottled::class);
});

it('applies persisted limiter configuration overrides for package managed limiters', function (): void {
    Event::fake();

    RateLimiter::for('managed-api', fn ($request): Limit => Limit::perMinute(60)->by($request->ip()));
    RateLimitConfig::create([
        'limiter_name' => 'managed-api',
        'max_attempts' => 1,
        'decay_seconds' => 60,
        'alert_threshold' => 80,
    ]);

    Route::middleware(RateLimitInstrumenter::class.':managed-api')->get('/managed-api', fn (): string => 'ok');

    $this->get('/managed-api')->assertOk();
    $this->get('/managed-api')->assertStatus(429);

    Event::assertDispatched(RateLimitThrottled::class, fn (RateLimitThrottled $event): bool => $event->limiterName === 'managed-api' &&
        $event->maxAttempts === 1);
});

it('does not dispatch events when disabled', function (): void {
    Event::fake();
    config()->set('rate-limit-dashboard.enabled', false);

    Route::middleware(RateLimitInstrumenter::class)->get('/test-disabled', fn (): string => 'ok')->name('test.disabled');

    $response = $this->get('/test-disabled');
    $response->assertOk();

    Event::assertNotDispatched(RateLimitHit::class);
});
