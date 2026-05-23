<?php

declare(strict_types=1);

namespace Sa\RateLimitDashboard\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $limiter_name
 * @property string $time_window
 * @property int $total_requests
 * @property int $throttled_requests
 * @property Carbon $window_start
 */
class RateLimitHistorySummary extends Model
{
    protected $fillable = [
        'limiter_name',
        'time_window',
        'total_requests',
        'throttled_requests',
        'window_start',
    ];

    protected $casts = [
        'window_start' => 'datetime',
        'total_requests' => 'integer',
        'throttled_requests' => 'integer',
    ];
}
