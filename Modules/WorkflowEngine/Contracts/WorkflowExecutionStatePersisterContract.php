<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Contracts;

use Modules\WorkflowEngine\DTOs\WorkflowExecutionResultDTO;
use Modules\WorkflowEngine\DTOs\WorkflowGraphDTO;
use Modules\WorkflowEngine\DTOs\WorkflowStepExecutionResultDTO;
use Modules\WorkflowEngine\Models\WorkflowRun;
use Modules\WorkflowEngine\Models\WorkflowRunStep;
use Throwable;

interface WorkflowExecutionStatePersisterContract
{
    /**
     * @param  list<list<string>>  $layers
     */
    public function initializeSteps(WorkflowRun $run, WorkflowGraphDTO $graph, array $layers): void;

    public function markRunRunning(WorkflowRun $run): void;

    public function markStepRunning(WorkflowRunStep $step): void;

    public function markStepSuccess(WorkflowRunStep $step, WorkflowStepExecutionResultDTO $result): void;

    public function markStepFailed(WorkflowRunStep $step, WorkflowStepExecutionResultDTO $result): void;

    /**
     * @param  array<string, mixed>  $output
     */
    public function markRunSuccess(WorkflowRun $run, array $output): WorkflowExecutionResultDTO;

    public function markRunFailed(
        WorkflowRun $run,
        WorkflowStepExecutionResultDTO $failedStep,
    ): WorkflowExecutionResultDTO;

    public function markRunCancelled(WorkflowRun $run): WorkflowExecutionResultDTO;

    public function markRunFailedByException(WorkflowRun $run, Throwable $throwable): WorkflowExecutionResultDTO;
}
