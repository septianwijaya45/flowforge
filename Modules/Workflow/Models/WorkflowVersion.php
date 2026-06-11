<?php

declare(strict_types=1);

namespace Modules\Workflow\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Modules\Auth\Models\User;
use Modules\Tenant\Concerns\BelongsToTenant;
use Modules\Tenant\Models\Tenant;
use Modules\WorkflowEngine\Models\WorkflowRun;

/**
 * Immutable snapshot of a workflow DAG definition.
 *
 * @property string $id
 * @property string $tenant_id
 * @property string $workflow_id
 * @property int $version_number
 * @property array<string, mixed> $definition
 * @property string|null $definition_hash
 * @property string|null $change_summary
 * @property int|null $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Tenant $tenant
 * @property-read Workflow $workflow
 * @property-read User|null $creator
 * @property-read Collection<int, WorkflowRun> $runs
 */
class WorkflowVersion extends Model
{
    use BelongsToTenant;
    use HasUuids;

    /** @var list<string> */
    protected $fillable = [
        'tenant_id',
        'workflow_id',
        'version_number',
        'definition',
        'definition_hash',
        'change_summary',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'definition' => 'array',
            'version_number' => 'integer',
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
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<WorkflowRun, $this>
     */
    public function runs(): HasMany
    {
        return $this->hasMany(WorkflowRun::class);
    }
}
