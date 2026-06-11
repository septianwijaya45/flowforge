<?php

declare(strict_types=1);

namespace Modules\Trigger\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Modules\Auth\Models\User;
use Modules\Tenant\Concerns\BelongsToTenant;
use Modules\Tenant\Models\Tenant;
use Modules\Trigger\Enums\TriggerType;
use Modules\Workflow\Models\Workflow;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $workflow_id
 * @property TriggerType $type
 * @property string $name
 * @property bool $is_active
 * @property array<string, mixed>|null $config
 * @property string|null $webhook_token
 * @property Carbon|null $last_triggered_at
 * @property int|null $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Tenant $tenant
 * @property-read Workflow $workflow
 * @property-read User|null $creator
 */
class WorkflowTrigger extends Model
{
    use BelongsToTenant;
    use HasUuids;
    use SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'tenant_id',
        'workflow_id',
        'type',
        'name',
        'is_active',
        'config',
        'webhook_token',
        'last_triggered_at',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => TriggerType::class,
            'is_active' => 'boolean',
            'config' => 'array',
            'last_triggered_at' => 'datetime',
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
}
