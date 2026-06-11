<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Services;

use Modules\WorkflowEngine\Contracts\WorkflowRunBroadcastContract;
use Modules\WorkflowEngine\Models\WorkflowRun;
use Modules\WorkflowEngine\Models\WorkflowRunStep;

class NullWorkflowRunBroadcaster implements WorkflowRunBroadcastContract
{
    public function runUpdated(WorkflowRun $run): void {}

    public function stepUpdated(WorkflowRunStep $step): void {}
}
