<?php

namespace Sa\RateLimitDashboard\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RateLimitThresholdReached extends Notification
{
    use Queueable;

    public function __construct(
        public string $limiterName,
        public float $utilisation,
        public int $threshold,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return config('rate-limit-dashboard.notifications.channels', ['mail']);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Rate limit threshold reached')
            ->line("Limiter {$this->limiterName} is at {$this->utilisation}% utilisation.")
            ->line("Configured threshold: {$this->threshold}%.");
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'limiter_name' => $this->limiterName,
            'utilisation' => $this->utilisation,
            'threshold' => $this->threshold,
        ];
    }
}
