<?php

use Illuminate\Http\Request;
use Sa\RateLimitDashboard\Events\RateLimitThrottled;

it('assigns properties correctly upon instantiation', function (): void {
    $request = Request::create('/throttle-url', 'POST');

    $event = new RateLimitThrottled(
        'login_limiter',
        'ip_127_0_0_1',
        5,
        $request
    );

    expect($event->limiterName)->toBe('login_limiter')
        ->and($event->limiterKey)->toBe('ip_127_0_0_1')
        ->and($event->maxAttempts)->toBe(5)
        ->and($event->request)->toBe($request);
});
