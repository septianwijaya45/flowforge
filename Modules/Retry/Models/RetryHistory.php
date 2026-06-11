<?php

declare(strict_types=1);

namespace Modules\Retry\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Modules\Retry\Enums\RetryHistoryStatus;
use Modules\Tenant\Models\Tenant;

/**
 * @property string $id
 * @property string|null $tenant_id
 * @property string $retryable_type
 * @property string $retryable_id
 * @property int $attempt
 * @property int $max_attempts
 * @property string $strategy
 * @property int $delay_seconds
 * @property RetryHistoryStatus $status
 * @property array<string, mixed>|null $error
 * @property array<string, mixed>|null $metadata
 * @property Carbon $scheduled_at
 * @property Carbon|null $attempted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Model $retryable
 * @property-read Tenant|null $tenant
 */
class RetryHistory extends Model
{
    use HasUuids;

    /** @var list<string> */
    protected $fillable = [
        'tenant_id',
        'retryable_type',
        'retryable_id',
        'attempt',
        'max_attempts',
        'strategy',
        'delay_seconds',
        'status',
        'error',
        'metadata',
        'scheduled_at',
        'attempted_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attempt' => 'integer',
            'max_attempts' => 'integer',
            'delay_seconds' => 'integer',
            'status' => RetryHistoryStatus::class,
            'error' => 'array',
            'metadata' => 'array',
            'scheduled_at' => 'datetime',
            'attempted_at' => 'datetime',
        ];
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function retryable(): MorphTo
    {
        return $this->morphTo();
    }
}
