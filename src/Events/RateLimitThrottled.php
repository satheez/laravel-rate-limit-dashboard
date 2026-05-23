<?php

namespace Sa\RateLimitDashboard\Events;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RateLimitThrottled
{
    public function __construct(
        public string $limiterName,
        public string $limiterKey,
        public int $maxAttempts,
        public int $currentAttempts,
        public ?string $requestMethod,
        public ?string $urlPath,
        public ?string $ipAddress,
        public int|string|null $userId,
        public ?string $apiToken,
    ) {}

    public static function fromRequest(
        string $limiterName,
        string $limiterKey,
        int $maxAttempts,
        int $currentAttempts,
        Request $request
    ): self {
        return new self(
            $limiterName,
            $limiterKey,
            $maxAttempts,
            $currentAttempts,
            $request->method(),
            $request->path(),
            $request->ip(),
            $request->user()?->getAuthIdentifier(),
            self::hashToken($request->bearerToken())
        );
    }

    protected static function hashToken(?string $token): ?string
    {
        if (blank($token)) {
            return null;
        }

        return Str::limit(hash('sha256', $token), 16, '');
    }
}
