<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Modules\Auth\Models\User;
use Modules\Tenant\Concerns\BelongsToTenant;
use Modules\Tenant\Models\Tenant;
use Modules\Workflow\Models\Workflow;
use Modules\Workflow\Models\WorkflowVersion;
use Modules\WorkflowEngine\Enums\WorkflowRunStatus;
use Modules\WorkflowEngine\Enums\WorkflowTriggerType;

/**
 * A single execution instance of a workflow version.
 *
 * @property string $id
 * @property string $tenant_id
 * @property string $workflow_id
 * @property string $workflow_version_id
 * @property WorkflowRunStatus $status
 * @property WorkflowTriggerType $trigger_type
 * @property array<string, mixed>|null $trigger_payload
 * @property array<string, mixed>|null $input
 * @property array<string, mixed>|null $output
 * @property array<string, mixed>|null $error
 * @property Carbon|null $started_at
 * @property Carbon|null $completed_at
 * @property int|null $triggered_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Tenant $tenant
 * @property-read Workflow $workflow
 * @property-read WorkflowVersion $workflowVersion
 * @property-read User|null $triggeredByUser
 * @property-read Collection<int, WorkflowRunStep> $steps
 */
class WorkflowRun extends Model
{
    use BelongsToTenant;
    use HasUuids;

    /** @var list<string> */
    protected $fillable = [
        'tenant_id',
        'workflow_id',
        'workflow_version_id',
        'status',
        'trigger_type',
        'trigger_payload',
        'input',
        'output',
        'error',
        'started_at',
        'completed_at',
        'triggered_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => WorkflowRunStatus::class,
            'trigger_type' => WorkflowTriggerType::class,
            'trigger_payload' => 'array',
            'input' => 'array',
            'output' => 'array',
            'error' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Workflow, $this>
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * @return BelongsTo<WorkflowVersion, $this>
     */
    public function workflowVersion(): BelongsTo
    {
        return $this->belongsTo(WorkflowVersion::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function triggeredByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    /**
     * @return HasMany<WorkflowRunStep, $this>
     */
    public function steps(): HasMany
    {
        return $this->hasMany(WorkflowRunStep::class)->orderBy('execution_order');
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeInStatus(Builder $query, WorkflowRunStatus $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeTerminal(Builder $query): Builder
    {
        return $query->whereIn('status', [
            WorkflowRunStatus::Completed,
            WorkflowRunStatus::Failed,
            WorkflowRunStatus::Cancelled,
            WorkflowRunStatus::TimedOut,
        ]);
    }
}
