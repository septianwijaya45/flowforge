<?php

declare(strict_types=1);

namespace Modules\Monitoring\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\WorkflowEngine\Models\WorkflowRunStep;

/**
 * @mixin WorkflowRunStep
 */
class WorkflowRunStepResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'workflow_run_id' => $this->workflow_run_id,
            'node_id' => $this->node_id,
            'node_type' => $this->node_type->value,
            'node_label' => $this->node_label,
            'status' => $this->status->value,
            'attempt' => $this->attempt,
            'execution_order' => $this->execution_order,
            'duration_ms' => $this->duration_ms,
            'error' => $this->error,
            'started_at' => $this->started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
        ];
    }
}
