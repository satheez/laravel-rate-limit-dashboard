<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Sa\RateLimitDashboard\Events\RateLimitThrottled;

it('assigns properties correctly upon instantiation', function (): void {
    $request = Request::create('/throttle-url', 'POST');

    $event = RateLimitThrottled::fromRequest(
        'login_limiter',
        'ip_127_0_0_1',
        5,
        6,
        $request
    );

    expect($event->limiterName)->toBe('login_limiter')
        ->and($event->limiterKey)->toBe('ip_127_0_0_1')
        ->and($event->maxAttempts)->toBe(5)
        ->and($event->currentAttempts)->toBe(6)
        ->and($event->requestMethod)->toBe('POST')
        ->and($event->urlPath)->toBe('throttle-url');
});
