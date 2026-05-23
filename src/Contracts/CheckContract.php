<?php

declare(strict_types=1);

namespace Sa\RateLimitDashboard\Contracts;

use Sa\RateLimitDashboard\Support\CheckResult;

interface CheckContract
{
    public function run(): CheckResult;
}
