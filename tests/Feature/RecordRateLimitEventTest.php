<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Sa\RateLimitDashboard\Events\RateLimitHit;
use Sa\RateLimitDashboard\Events\RateLimitThrottled;
use Sa\RateLimitDashboard\Models\RateLimitEvent;
use Sa\RateLimitDashboard\Models\RateLimitHistorySummary;

it('records rate limit hit event', function (): void {
    $request = Request::create('/test', 'GET', [], [], [], [
        'HTTP_AUTHORIZATION' => 'Bearer plain-secret-token',
    ]);
    $event = RateLimitHit::fromRequest('test_limiter', 'test_key', 60, 1, $request);

    expect(unserialize(serialize($event)))->toBeInstanceOf(RateLimitHit::class);

    Event::dispatch($event);

    expect(RateLimitEvent::count())->toBe(1);

    $model = RateLimitEvent::first();
    expect($model->limiter_name)->toBe('test_limiter')
        ->and($model->limiter_key)->toBe('test_key')
        ->and($model->max_attempts)->toBe(60)
        ->and($model->current_attempts)->toBe(1)
        ->and($model->status)->toBe('hit')
        ->and($model->url_path)->toBe('test')
        ->and($model->api_token)->not->toBe('plain-secret-token')
        ->and(strlen((string) $model->api_token))->toBe(16);

    expect(RateLimitHistorySummary::where('limiter_name', 'test_limiter')->count())->toBe(3)
        ->and(RateLimitHistorySummary::sum('total_requests'))->toBe(3)
        ->and(RateLimitHistorySummary::sum('throttled_requests'))->toBe(0);
});

it('records rate limit throttled event', function (): void {
    $request = Request::create('/test', 'POST');
    $event = RateLimitThrottled::fromRequest('test_limiter', 'test_key', 60, 61, $request);

    Event::dispatch($event);

    expect(RateLimitEvent::count())->toBe(1);

    $model = RateLimitEvent::first();
    expect($model->limiter_name)->toBe('test_limiter')
        ->and($model->max_attempts)->toBe(60)
        ->and($model->current_attempts)->toBe(61)
        ->and($model->status)->toBe('throttled')
        ->and($model->request_method)->toBe('POST');

    expect(RateLimitHistorySummary::where('limiter_name', 'test_limiter')->count())->toBe(3)
        ->and(RateLimitHistorySummary::sum('total_requests'))->toBe(3)
        ->and(RateLimitHistorySummary::sum('throttled_requests'))->toBe(3);
});
