<?php

declare(strict_types=1);

namespace Sa\RateLimitDashboard\Console;

use Illuminate\Console\Command;
use Sa\RateLimitDashboard\Models\RateLimitEvent;

class PruneRateLimitEventsCommand extends Command
{
    protected $signature = 'rate-limit:prune {--days= : Override configured retention days}';

    protected $description = 'Prune raw rate-limit events older than the configured retention period.';

    public function handle(): int
    {
        $days = $this->option('days') ?? config('rate-limit-dashboard.retention_days');

        if (blank($days)) {
            $this->components->info('Rate-limit event retention is disabled.');

            return self::SUCCESS;
        }

        $deleted = RateLimitEvent::query()
            ->where('created_at', '<', now()->subDays((int) $days))
            ->delete();

        $this->components->info("Pruned {$deleted} rate-limit event(s).");

        return self::SUCCESS;
    }
}
