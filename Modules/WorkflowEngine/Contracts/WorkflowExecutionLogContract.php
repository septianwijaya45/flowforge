<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Contracts;

use Modules\WorkflowEngine\Models\WorkflowRun;
use Modules\WorkflowEngine\Models\WorkflowRunStep;

interface WorkflowExecutionLogContract
{
    public function runStarted(WorkflowRun $run): void;

    public function runSucceeded(WorkflowRun $run): void;

    /**
     * @param  array<string, mixed>|null  $error
     */
    public function runFailed(WorkflowRun $run, ?string $nodeId = null, ?array $error = null): void;

    public function runCancelled(WorkflowRun $run): void;

    public function stepRunning(WorkflowRunStep $step): void;

    public function stepSucceeded(WorkflowRunStep $step, ?int $durationMs = null): void;

    /**
     * @param  array<string, mixed>|null  $error
     */
    public function stepFailed(WorkflowRunStep $step, ?array $error = null, ?int $durationMs = null): void;
}
