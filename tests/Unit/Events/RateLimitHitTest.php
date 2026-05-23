<?php

use Illuminate\Http\Request;
use Sa\RateLimitDashboard\Events\RateLimitHit;

it('assigns properties correctly upon instantiation', function (): void {
    $request = Request::create('/test-url', 'GET');

    $event = new RateLimitHit(
        'api_limiter',
        'api_key_123',
        100,
        5,
        $request
    );

    expect($event->limiterName)->toBe('api_limiter')
        ->and($event->limiterKey)->toBe('api_key_123')
        ->and($event->maxAttempts)->toBe(100)
        ->and($event->currentAttempts)->toBe(5)
        ->and($event->request)->toBe($request);
});
