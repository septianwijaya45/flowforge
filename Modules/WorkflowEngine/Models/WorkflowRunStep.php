<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\Tenant\Concerns\BelongsToTenant;
use Modules\Tenant\Models\Tenant;
use Modules\WorkflowEngine\Enums\WorkflowNodeType;
use Modules\WorkflowEngine\Enums\WorkflowRunStepStatus;

/**
 * Runtime state for an individual DAG node within a workflow run.
 *
 * @property string $id
 * @property string $tenant_id
 * @property string $workflow_run_id
 * @property string $node_id
 * @property WorkflowNodeType $node_type
 * @property string|null $node_label
 * @property WorkflowRunStepStatus $status
 * @property int $attempt
 * @property int|null $execution_order
 * @property array<string, mixed>|null $input
 * @property array<string, mixed>|null $output
 * @property array<string, mixed>|null $error
 * @property Carbon|null $started_at
 * @property Carbon|null $completed_at
 * @property int|null $duration_ms
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Tenant $tenant
 * @property-read WorkflowRun $workflowRun
 */
class WorkflowRunStep extends Model
{
    use BelongsToTenant;
    use HasUuids;

    /** @var list<string> */
    protected $fillable = [
        'tenant_id',
        'workflow_run_id',
        'node_id',
        'node_type',
        'node_label',
        'status',
        'attempt',
        'execution_order',
        'input',
        'output',
        'error',
        'started_at',
        'completed_at',
        'duration_ms',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'node_type' => WorkflowNodeType::class,
            'status' => WorkflowRunStepStatus::class,
            'input' => 'array',
            'output' => 'array',
            'error' => 'array',
            'attempt' => 'integer',
            'execution_order' => 'integer',
            'duration_ms' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<WorkflowRun, $this>
     */
    public function workflowRun(): BelongsTo
    {
        return $this->belongsTo(WorkflowRun::class);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeInStatus(Builder $query, WorkflowRunStepStatus $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeForNode(Builder $query, string $nodeId): Builder
    {
        return $query->where('node_id', $nodeId);
    }
}
