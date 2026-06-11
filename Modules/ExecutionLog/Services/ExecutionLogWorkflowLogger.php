<?php

declare(strict_types=1);

namespace Modules\ExecutionLog\Services;

use Modules\ExecutionLog\Contracts\ExecutionLogWriterServiceContract;
use Modules\ExecutionLog\DTOs\AppendExecutionLogDTO;
use Modules\ExecutionLog\Enums\ExecutionLogLevel;
use Modules\WorkflowEngine\Contracts\WorkflowExecutionLogContract;
use Modules\WorkflowEngine\Models\WorkflowRun;
use Modules\WorkflowEngine\Models\WorkflowRunStep;

class ExecutionLogWorkflowLogger implements WorkflowExecutionLogContract
{
    public function __construct(
        private readonly ExecutionLogWriterServiceContract $writer,
    ) {}

    public function runStarted(WorkflowRun $run): void
    {
        $this->write($run, ExecutionLogLevel::Info, 'Workflow run started', context: [
            'trigger_type' => $run->trigger_type->value,
        ]);
    }

    public function runSucceeded(WorkflowRun $run): void
    {
        $this->write($run, ExecutionLogLevel::Info, 'Workflow run completed successfully');
    }

    public function runFailed(WorkflowRun $run, ?string $nodeId = null, ?array $error = null): void
    {
        $this->write($run, ExecutionLogLevel::Error, 'Workflow run failed', $nodeId, [
            'error' => $this->sanitizeError($error),
        ]);
    }

    public function runCancelled(WorkflowRun $run): void
    {
        $this->write($run, ExecutionLogLevel::Warning, 'Workflow run cancelled');
    }

    public function stepRunning(WorkflowRunStep $step): void
    {
        $this->writeStep($step, ExecutionLogLevel::Info, 'Step execution started');
    }

    public function stepSucceeded(WorkflowRunStep $step, ?int $durationMs = null): void
    {
        $this->writeStep($step, ExecutionLogLevel::Info, 'Step executed successfully', [
            'duration_ms' => $durationMs ?? $step->duration_ms,
        ]);
    }

    public function stepFailed(WorkflowRunStep $step, ?array $error = null, ?int $durationMs = null): void
    {
        $this->writeStep($step, ExecutionLogLevel::Error, 'Step execution failed', [
            'duration_ms' => $durationMs ?? $step->duration_ms,
            'error' => $this->sanitizeError($error),
        ]);
    }

    /**
     * @param  array<string, mixed>|null  $context
     */
    private function write(
        WorkflowRun $run,
        ExecutionLogLevel $level,
        string $message,
        ?string $nodeId = null,
        ?array $context = null,
        ?string $stepId = null,
    ): void {
        $this->writer->log(new AppendExecutionLogDTO(
            tenantId: $run->tenant_id,
            level: $level,
            message: $message,
            workflowId: $run->workflow_id,
            workflowRunId: $run->id,
            workflowRunStepId: $stepId,
            nodeId: $nodeId,
            context: $context,
        ));
    }

    /**
     * @param  array<string, mixed>|null  $context
     */
    private function writeStep(
        WorkflowRunStep $step,
        ExecutionLogLevel $level,
        string $message,
        ?array $context = null,
    ): void {
        $run = $step->relationLoaded('workflowRun')
            ? $step->workflowRun
            : $step->workflowRun()->first(['id', 'tenant_id', 'workflow_id']);

        if ($run === null) {
            return;
        }

        $this->write(
            run: $run,
            level: $level,
            message: $message,
            nodeId: $step->node_id,
            context: array_merge([
                'node_type' => $step->node_type->value,
                'attempt' => $step->attempt,
            ], $context ?? []),
            stepId: $step->id,
        );
    }

    /**
     * @param  array<string, mixed>|null  $error
     * @return array<string, mixed>|null
     */
    private function sanitizeError(?array $error): ?array
    {
        if ($error === null) {
            return null;
        }

        return array_filter([
            'message' => $error['message'] ?? null,
            'code' => $error['code'] ?? null,
        ]);
    }
}
