<?php

declare(strict_types=1);

namespace Sa\RateLimitDashboard\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $limiter_name
 * @property int|null $user_id
 * @property string $action
 * @property array<string, mixed>|null $before
 * @property array<string, mixed>|null $after
 * @property string|null $reason
 */
class RateLimitConfigAudit extends Model
{
    protected $fillable = [
        'limiter_name',
        'user_id',
        'action',
        'before',
        'after',
        'reason',
    ];

    protected $casts = [
        'before' => 'array',
        'after' => 'array',
    ];
}
