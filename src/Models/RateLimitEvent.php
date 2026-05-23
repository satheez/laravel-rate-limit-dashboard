<?php

declare(strict_types=1);

namespace Sa\RateLimitDashboard\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $limiter_name
 * @property string $limiter_key
 * @property int $max_attempts
 * @property int $current_attempts
 * @property string|null $request_method
 * @property string|null $url_path
 * @property string $status
 * @property string|null $ip_address
 * @property int|null $user_id
 * @property string|null $api_token
 * @property Carbon $created_at
 */
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
