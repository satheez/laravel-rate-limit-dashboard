<?php

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Sa\RateLimitDashboard\Events\RateLimitHit;
use Sa\RateLimitDashboard\Events\RateLimitThrottled;
use Sa\RateLimitDashboard\Http\Middleware\RateLimitInstrumenter;

beforeEach(function (): void {
    config()->set('rate-limit-dashboard.enabled', true);
});

it('dispatches RateLimitHit event when not throttled', function (): void {
    Event::fake();

    Route::middleware(RateLimitInstrumenter::class)->get('/test-route', fn (): string => 'ok')->name('test.route');

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

it('does not dispatch events when disabled', function (): void {
    Event::fake();
    config()->set('rate-limit-dashboard.enabled', false);

    Route::middleware(RateLimitInstrumenter::class)->get('/test-disabled', fn (): string => 'ok')->name('test.disabled');

    $response = $this->get('/test-disabled');
    $response->assertOk();

    Event::assertNotDispatched(RateLimitHit::class);
});
