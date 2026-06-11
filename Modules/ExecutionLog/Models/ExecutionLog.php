<?php

declare(strict_types=1);

namespace Modules\ExecutionLog\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Modules\ExecutionLog\Enums\ExecutionLogLevel;

/**
 * Append-only workflow execution log entry stored on the execution_logs connection.
 *
 * @property string $id
 * @property string $tenant_id
 * @property string|null $workflow_id
 * @property string|null $workflow_run_id
 * @property string|null $workflow_run_step_id
 * @property string|null $node_id
 * @property ExecutionLogLevel $level
 * @property string $message
 * @property array<string, mixed>|null $context
 * @property Carbon $logged_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class ExecutionLog extends Model
{
    use HasUuids;

    public function getConnectionName(): ?string
    {
        return (string) config('execution_log.connection', 'execution_logs');
    }

    protected $table = 'execution_logs';

    /** @var list<string> */
    protected $fillable = [
        'tenant_id',
        'workflow_id',
        'workflow_run_id',
        'workflow_run_step_id',
        'node_id',
        'level',
        'message',
        'context',
        'logged_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'level' => ExecutionLogLevel::class,
            'context' => 'array',
            'logged_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeForTenant(Builder $query, string $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeForRun(Builder $query, string $workflowRunId): Builder
    {
        return $query->where('workflow_run_id', $workflowRunId);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeLoggedBefore(Builder $query, Carbon $cutoff): Builder
    {
        return $query->where('logged_at', '<', $cutoff);
    }
}
