<?php

declare(strict_types=1);

namespace Modules\Monitoring\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Monitoring\Http\Resources\WorkflowRunStepResource;
use Modules\WorkflowEngine\Models\WorkflowRunStep;

class WorkflowRunStepStatusChanged implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public WorkflowRunStep $step,
    ) {}

    /**
     * @return list<PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('workflow-runs.'.$this->step->workflow_run_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'step.updated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'run_id' => $this->step->workflow_run_id,
            'step' => (new WorkflowRunStepResource($this->step))->resolve(),
        ];
    }
}
