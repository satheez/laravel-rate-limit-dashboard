<?php

namespace Sa\RateLimitDashboard\Models;

use Illuminate\Database\Eloquent\Model;

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
