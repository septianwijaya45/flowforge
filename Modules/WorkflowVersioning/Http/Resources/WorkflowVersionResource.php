<?php

declare(strict_types=1);

namespace Modules\WorkflowVersioning\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Workflow\Models\Workflow;
use Modules\Workflow\Models\WorkflowVersion;

/**
 * @mixin WorkflowVersion
 */
class WorkflowVersionResource extends JsonResource
{
    public function __construct(
        WorkflowVersion $resource,
        private readonly ?Workflow $parentWorkflow = null,
        private readonly bool $includeDefinition = true,
    ) {
        parent::__construct($resource);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $workflow = $this->parentWorkflow ?? $this->resource->workflow;

        $data = [
            'id' => $this->id,
            'workflow_id' => $this->workflow_id,
            'version_number' => $this->version_number,
            'definition_hash' => $this->definition_hash,
            'change_summary' => $this->change_summary,
            'is_current' => $workflow?->current_version_id === $this->id,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];

        if ($this->includeDefinition) {
            $data['definition'] = $this->definition;
        }

        return $data;
    }
}
