<?php

namespace Sa\RateLimitDashboard\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use Sa\RateLimitDashboard\Models\RateLimitConfig;
use Sa\RateLimitDashboard\Models\RateLimitEvent;
use Sa\RateLimitDashboard\Notifications\RateLimitThresholdReached;

class CheckRateLimitAlertsCommand extends Command
{
    protected $signature = 'rate-limit:check-alerts';

    protected $description = 'Send notifications for limiters above their configured utilisation threshold.';

    public function handle(): int
    {
        if (! (bool) config('rate-limit-dashboard.notifications.enabled', false)) {
            $this->components->info('Rate-limit notifications are disabled.');

            return self::SUCCESS;
        }

        $mailTo = config('rate-limit-dashboard.notifications.mail_to');

        if (blank($mailTo)) {
            $this->components->warn('No rate-limit notification recipient configured.');

            return self::SUCCESS;
        }

        $sent = 0;

        foreach (RateLimitConfig::query()->get() as $config) {
            $latestEvent = RateLimitEvent::query()
                ->where('limiter_name', $config->limiter_name)
                ->orderByDesc('created_at')
                ->first();
            if (! $latestEvent instanceof RateLimitEvent) {
                continue;
            }
            if ($config->max_attempts <= 0) {
                continue;
            }

            $utilisation = ($latestEvent->current_attempts / $config->max_attempts) * 100;

            if ($utilisation < $config->alert_threshold) {
                continue;
            }

            Notification::route('mail', (string) $mailTo)
                ->notify(new RateLimitThresholdReached($config->limiter_name, round($utilisation, 2), $config->alert_threshold));

            $sent++;
        }

        $this->components->info("Sent {$sent} rate-limit alert notification(s).");

        return self::SUCCESS;
    }
}
