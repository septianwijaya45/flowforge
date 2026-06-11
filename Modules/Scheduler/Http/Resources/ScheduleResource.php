<?php

declare(strict_types=1);

namespace Modules\Scheduler\Http\Resources;

use Cron\CronExpression;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Trigger\Models\WorkflowTrigger;

/**
 * @mixin WorkflowTrigger
 */
class ScheduleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $expression = $this->config['expression'] ?? null;
        $nextRunAt = null;

        if (is_string($expression) && $expression !== '') {
            try {
                $nextRunAt = (new CronExpression($expression))->getNextRunDate()->format(DATE_ATOM);
            } catch (\Throwable) {
                $nextRunAt = null;
            }
        }

        return [
            'id' => $this->id,
            'workflow_id' => $this->workflow_id,
            'workflow_name' => $this->relationLoaded('workflow') ? $this->workflow?->name : null,
            'workflow_slug' => $this->relationLoaded('workflow') ? $this->workflow?->slug : null,
            'name' => $this->name,
            'cron_expression' => $expression,
            'is_active' => $this->is_active,
            'next_run_at' => $nextRunAt,
            'last_triggered_at' => $this->last_triggered_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
