<?php

namespace Sa\RateLimitDashboard\Events;

use Illuminate\Http\Request;

class RateLimitThrottled
{
    /**
     * @var string
     */
    public $limiterName;

    /**
     * @var string
     */
    public $limiterKey;

    /**
     * @var int
     */
    public $maxAttempts;

    /**
     * @var Request
     */
    public $request;

    public function __construct(
        string $limiterName,
        string $limiterKey,
        int $maxAttempts,
        Request $request
    ) {
        $this->limiterName = $limiterName;
        $this->limiterKey = $limiterKey;
        $this->maxAttempts = $maxAttempts;
        $this->request = $request;
    }
}
