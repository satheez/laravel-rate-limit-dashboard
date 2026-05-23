<?php

namespace Sa\RateLimitDashboard\Models;

use Illuminate\Database\Eloquent\Model;

class RateLimitConfig extends Model
{
    protected $primaryKey = 'limiter_name';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'limiter_name',
        'max_attempts',
        'decay_seconds',
        'overrides',
        'alert_threshold',
    ];

    protected $casts = [
        'overrides' => 'array',
        'max_attempts' => 'integer',
        'decay_seconds' => 'integer',
        'alert_threshold' => 'integer',
    ];
}
