<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Services;

use Illuminate\Support\Carbon;
use Modules\WorkflowEngine\Contracts\WorkflowExecutionLogContract;
use Modules\WorkflowEngine\Contracts\WorkflowExecutionStatePersisterContract;
use Modules\WorkflowEngine\Contracts\WorkflowRunBroadcastContract;
use Modules\WorkflowEngine\DTOs\WorkflowExecutionResultDTO;
use Modules\WorkflowEngine\DTOs\WorkflowGraphDTO;
use Modules\WorkflowEngine\DTOs\WorkflowStepExecutionResultDTO;
use Modules\WorkflowEngine\Enums\WorkflowRunStatus;
use Modules\WorkflowEngine\Enums\WorkflowRunStepStatus;
use Modules\WorkflowEngine\Models\WorkflowRun;
use Modules\WorkflowEngine\Models\WorkflowRunStep;
use Throwable;

class WorkflowExecutionStatePersister implements WorkflowExecutionStatePersisterContract
{
    public function __construct(
        private readonly WorkflowRunBroadcastContract $broadcaster,
        private readonly WorkflowExecutionLogContract $executionLogger,
    ) {}

    public function initializeSteps(WorkflowRun $run, WorkflowGraphDTO $graph, array $layers): void
    {
        if ($run->steps()->exists()) {
            return;
        }

        $nodesById = [];

        foreach ($graph->nodes as $node) {
            $nodesById[$node->id] = $node;
        }

        $executionOrder = 0;

        foreach ($layers as $layer) {
            foreach ($layer as $nodeId) {
                $node = $nodesById[$nodeId];

                WorkflowRunStep::query()->create([
                    'tenant_id' => $run->tenant_id,
                    'workflow_run_id' => $run->id,
                    'node_id' => $node->id,
                    'node_type' => $node->type,
                    'node_label' => $node->config['label'] ?? null,
                    'status' => WorkflowRunStepStatus::Pending,
                    'execution_order' => $executionOrder,
                ]);

                $executionOrder++;
            }
        }

        $this->broadcaster->runUpdated($run->refresh()->load('steps'));
    }

    public function markRunRunning(WorkflowRun $run): void
    {
        $run->forceFill([
            'status' => WorkflowRunStatus::Running,
            'started_at' => Carbon::now(),
        ])->save();

        $this->broadcaster->runUpdated($run->refresh());
        $this->executionLogger->runStarted($run);
    }

    public function markStepRunning(WorkflowRunStep $step): void
    {
        $step->forceFill([
            'status' => WorkflowRunStepStatus::Running,
            'started_at' => Carbon::now(),
        ])->save();

        $this->broadcaster->stepUpdated($step->refresh());
        $this->executionLogger->stepRunning($step);
    }

    public function markStepSuccess(WorkflowRunStep $step, WorkflowStepExecutionResultDTO $result): void
    {
        $completedAt = Carbon::now();

        $step->forceFill([
            'status' => WorkflowRunStepStatus::Success,
            'output' => $result->output,
            'error' => null,
            'completed_at' => $completedAt,
            'duration_ms' => $result->durationMs ?? $this->calculateDurationMs($step->started_at, $completedAt),
        ])->save();

        $this->broadcaster->stepUpdated($step->refresh());
        $this->executionLogger->stepSucceeded(
            $step,
            $result->durationMs ?? $step->duration_ms,
        );
    }

    public function markStepFailed(WorkflowRunStep $step, WorkflowStepExecutionResultDTO $result): void
    {
        $completedAt = Carbon::now();

        $step->forceFill([
            'status' => WorkflowRunStepStatus::Failed,
            'output' => null,
            'error' => $result->error,
            'completed_at' => $completedAt,
            'duration_ms' => $result->durationMs ?? $this->calculateDurationMs($step->started_at, $completedAt),
        ])->save();

        $this->broadcaster->stepUpdated($step->refresh());
        $this->executionLogger->stepFailed(
            $step,
            $result->error,
            $result->durationMs ?? $step->duration_ms,
        );
    }

    public function markRunSuccess(WorkflowRun $run, array $output): WorkflowExecutionResultDTO
    {
        $run->forceFill([
            'status' => WorkflowRunStatus::Success,
            'output' => $output,
            'error' => null,
            'completed_at' => Carbon::now(),
        ])->save();

        $this->broadcaster->runUpdated($run->refresh()->load('steps'));
        $this->executionLogger->runSucceeded($run);

        return WorkflowExecutionResultDTO::success($run->id, $output);
    }

    public function markRunFailed(
        WorkflowRun $run,
        WorkflowStepExecutionResultDTO $failedStep,
    ): WorkflowExecutionResultDTO {
        $error = $failedStep->error ?? ['message' => 'Workflow step failed.'];

        $run->forceFill([
            'status' => WorkflowRunStatus::Failed,
            'error' => $error,
            'completed_at' => Carbon::now(),
        ])->save();

        $this->broadcaster->runUpdated($run->refresh()->load('steps'));
        $this->executionLogger->runFailed($run, $failedStep->nodeId, $error);

        return WorkflowExecutionResultDTO::failed($run->id, $failedStep->nodeId, $error);
    }

    public function markRunCancelled(WorkflowRun $run): WorkflowExecutionResultDTO
    {
        $run->forceFill([
            'status' => WorkflowRunStatus::Cancelled,
            'completed_at' => Carbon::now(),
        ])->save();

        $run->steps()
            ->where('status', WorkflowRunStepStatus::Pending)
            ->update(['status' => WorkflowRunStepStatus::Cancelled]);

        $this->broadcaster->runUpdated($run->refresh()->load('steps'));
        $this->executionLogger->runCancelled($run);

        return WorkflowExecutionResultDTO::cancelled($run->id);
    }

    public function markRunFailedByException(WorkflowRun $run, Throwable $throwable): WorkflowExecutionResultDTO
    {
        $error = [
            'message' => $throwable->getMessage(),
            'exception' => $throwable::class,
        ];

        $run->forceFill([
            'status' => WorkflowRunStatus::Failed,
            'error' => $error,
            'completed_at' => Carbon::now(),
        ])->save();

        $this->broadcaster->runUpdated($run->refresh()->load('steps'));
        $this->executionLogger->runFailed($run, null, $error);

        return WorkflowExecutionResultDTO::failed($run->id, 'engine', $error);
    }

    private function calculateDurationMs(?Carbon $startedAt, Carbon $completedAt): ?int
    {
        if ($startedAt === null) {
            return null;
        }

        return (int) $startedAt->diffInMilliseconds($completedAt);
    }
}
