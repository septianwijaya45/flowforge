<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Contracts;

use Modules\WorkflowEngine\Models\WorkflowRun;
use Modules\WorkflowEngine\Models\WorkflowRunStep;

interface WorkflowRunBroadcastContract
{
    public function runUpdated(WorkflowRun $run): void;

    public function stepUpdated(WorkflowRunStep $step): void;
}
