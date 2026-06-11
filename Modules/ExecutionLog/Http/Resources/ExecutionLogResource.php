<?php

declare(strict_types=1);

namespace Modules\ExecutionLog\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\ExecutionLog\Models\ExecutionLog;

/**
 * @mixin ExecutionLog
 */
class ExecutionLogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'workflow_id' => $this->workflow_id,
            'workflow_run_id' => $this->workflow_run_id,
            'workflow_run_step_id' => $this->workflow_run_step_id,
            'node_id' => $this->node_id,
            'level' => $this->level->value,
            'message' => $this->message,
            'context' => $this->context,
            'logged_at' => $this->logged_at->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
