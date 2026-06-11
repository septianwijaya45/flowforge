<?php

declare(strict_types=1);

namespace Modules\Trigger\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\WorkflowEngine\Models\WorkflowRun;

/**
 * @mixin WorkflowRun
 */
class WorkflowRunResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'workflow_id' => $this->workflow_id,
            'workflow_version_id' => $this->workflow_version_id,
            'status' => $this->status->value,
            'trigger_type' => $this->trigger_type->value,
            'trigger_payload' => $this->trigger_payload,
            'input' => $this->input,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
