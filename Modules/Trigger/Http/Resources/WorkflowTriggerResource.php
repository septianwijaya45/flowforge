<?php

declare(strict_types=1);

namespace Modules\Trigger\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Trigger\Models\WorkflowTrigger;

/**
 * @mixin WorkflowTrigger
 */
class WorkflowTriggerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'workflow_id' => $this->workflow_id,
            'type' => $this->type->value,
            'name' => $this->name,
            'is_active' => $this->is_active,
            'config' => $this->config,
            'webhook_token' => $this->when(
                $this->type->value === 'webhook',
                $this->webhook_token,
            ),
            'webhook_url' => $this->when(
                $this->type->value === 'webhook' && filled($this->webhook_token),
                url('/api/v1/webhooks/'.$this->webhook_token),
            ),
            'last_triggered_at' => $this->last_triggered_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
