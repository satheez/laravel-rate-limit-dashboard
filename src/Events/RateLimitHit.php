<?php

namespace Sa\RateLimitDashboard\Events;

use Illuminate\Http\Request;

class RateLimitHit
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
     * @var int
     */
    public $currentAttempts;

    /**
     * @var Request
     */
    public $request;

    public function __construct(
        string $limiterName,
        string $limiterKey,
        int $maxAttempts,
        int $currentAttempts,
        Request $request
    ) {
        $this->limiterName = $limiterName;
        $this->limiterKey = $limiterKey;
        $this->maxAttempts = $maxAttempts;
        $this->currentAttempts = $currentAttempts;
        $this->request = $request;
    }
}
