<?php

namespace Sa\RateLimitDashboard\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class RateLimitEvent extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'limiter_name',
        'limiter_key',
        'max_attempts',
        'current_attempts',
        'request_method',
        'url_path',
        'status',
        'ip_address',
        'user_id',
        'api_token',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
