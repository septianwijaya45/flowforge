<?php

declare(strict_types=1);

namespace Modules\Monitoring\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\WorkflowEngine\Models\WorkflowRun;

/**
 * @mixin WorkflowRun
 */
class WorkflowRunMonitorResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'workflow_id' => $this->workflow_id,
            'workflow_name' => $this->whenLoaded('workflow', fn () => $this->workflow->name),
            'workflow_version_id' => $this->workflow_version_id,
            'status' => $this->status->value,
            'trigger_type' => $this->trigger_type->value,
            'input' => $this->input,
            'output' => $this->output,
            'error' => $this->error,
            'started_at' => $this->started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'steps' => $this->whenLoaded(
                'steps',
                fn () => WorkflowRunStepResource::collection($this->steps)->resolve(),
            ),
        ];
    }
}
