<?php

declare(strict_types=1);

namespace Sa\RateLimitDashboard\Support;

class CheckResult
{
    public function __construct(
        public string $code,
        public string $severity,
        public string $message,
        public ?string $action = null,
        public array $meta = [],
    ) {}

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'severity' => $this->severity,
            'message' => $this->message,
            'action' => $this->action,
            'meta' => $this->meta,
        ];
    }
}
