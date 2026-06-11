<?php

declare(strict_types=1);

namespace Modules\Monitoring\Services;

use Modules\Monitoring\Events\WorkflowRunStatusChanged;
use Modules\Monitoring\Events\WorkflowRunStepStatusChanged;
use Modules\WorkflowEngine\Contracts\WorkflowRunBroadcastContract;
use Modules\WorkflowEngine\Models\WorkflowRun;
use Modules\WorkflowEngine\Models\WorkflowRunStep;

class ReverbWorkflowRunBroadcaster implements WorkflowRunBroadcastContract
{
    public function runUpdated(WorkflowRun $run): void
    {
        broadcast(new WorkflowRunStatusChanged($run));
    }

    public function stepUpdated(WorkflowRunStep $step): void
    {
        broadcast(new WorkflowRunStepStatusChanged($step));
    }
}
