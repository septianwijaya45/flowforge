<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Services;

use Modules\WorkflowEngine\Contracts\WorkflowExecutionLogContract;
use Modules\WorkflowEngine\Models\WorkflowRun;
use Modules\WorkflowEngine\Models\WorkflowRunStep;

class NullWorkflowExecutionLogger implements WorkflowExecutionLogContract
{
    public function runStarted(WorkflowRun $run): void {}

    public function runSucceeded(WorkflowRun $run): void {}

    public function runFailed(WorkflowRun $run, ?string $nodeId = null, ?array $error = null): void {}

    public function runCancelled(WorkflowRun $run): void {}

    public function stepRunning(WorkflowRunStep $step): void {}

    public function stepSucceeded(WorkflowRunStep $step, ?int $durationMs = null): void {}

    public function stepFailed(WorkflowRunStep $step, ?array $error = null, ?int $durationMs = null): void {}
}
