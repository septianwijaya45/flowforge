<?php

declare(strict_types=1);

namespace Modules\Monitoring\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Monitoring\Http\Resources\WorkflowRunMonitorResource;
use Modules\WorkflowEngine\Models\WorkflowRun;

class WorkflowRunStatusChanged implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public WorkflowRun $run,
    ) {}

    /**
     * @return list<PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('workflow-runs.'.$this->run->id),
            new PrivateChannel('tenants.'.$this->run->tenant_id.'.workflow-runs'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'run.updated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $this->run->loadMissing(['workflow', 'steps']);

        return [
            'run' => (new WorkflowRunMonitorResource($this->run))->resolve(),
        ];
    }
}
