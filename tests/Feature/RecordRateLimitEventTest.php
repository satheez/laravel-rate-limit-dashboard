<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Sa\RateLimitDashboard\Events\RateLimitHit;
use Sa\RateLimitDashboard\Events\RateLimitThrottled;
use Sa\RateLimitDashboard\Models\RateLimitEvent;

it('records rate limit hit event', function (): void {
    $request = Request::create('/test', 'GET');
    $event = new RateLimitHit('test_limiter', 'test_key', 60, 1, $request);

    Event::dispatch($event);

    expect(RateLimitEvent::count())->toBe(1);

    $model = RateLimitEvent::first();
    expect($model->limiter_name)->toBe('test_limiter')
        ->and($model->limiter_key)->toBe('test_key')
        ->and($model->max_attempts)->toBe(60)
        ->and($model->current_attempts)->toBe(1)
        ->and($model->status)->toBe('hit')
        ->and($model->url_path)->toBe('test');
});

it('records rate limit throttled event', function (): void {
    $request = Request::create('/test', 'POST');
    $event = new RateLimitThrottled('test_limiter', 'test_key', 60, $request);

    Event::dispatch($event);

    expect(RateLimitEvent::count())->toBe(1);

    $model = RateLimitEvent::first();
    expect($model->limiter_name)->toBe('test_limiter')
        ->and($model->max_attempts)->toBe(60)
        ->and($model->current_attempts)->toBe(60) // Should default to maxAttempts
        ->and($model->status)->toBe('throttled')
        ->and($model->request_method)->toBe('POST');
});
