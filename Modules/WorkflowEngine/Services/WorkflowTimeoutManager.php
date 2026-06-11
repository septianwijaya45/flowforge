<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Services;

use DateTimeInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\WorkflowEngine\Contracts\WorkflowTimeoutManagerContract;
use Modules\WorkflowEngine\DTOs\EnforceWorkflowTimeoutDTO;
use Modules\WorkflowEngine\DTOs\WorkflowTimeoutResultDTO;
use Modules\WorkflowEngine\Enums\WorkflowRunStatus;
use Modules\WorkflowEngine\Enums\WorkflowRunStepStatus;
use Modules\WorkflowEngine\Exceptions\WorkflowExecutionException;
use Modules\WorkflowEngine\Models\WorkflowRun;
use Modules\WorkflowEngine\Models\WorkflowRunStep;

/**
 * Enforces workflow-level timeouts by cancelling active steps and updating run status.
 */
class WorkflowTimeoutManager implements WorkflowTimeoutManagerContract
{
    public function shouldTimeout(WorkflowRun $run, int $timeoutSeconds, ?DateTimeInterface $now = null): bool
    {
        if ($run->status !== WorkflowRunStatus::Running || $run->started_at === null) {
            return false;
        }

        $deadline = $run->started_at->copy()->addSeconds($timeoutSeconds);
        $currentTime = $now !== null ? Carbon::parse($now) : Carbon::now();

        return $currentTime->greaterThanOrEqualTo($deadline);
    }

    public function enforce(EnforceWorkflowTimeoutDTO $command): WorkflowTimeoutResultDTO
    {
        $run = WorkflowRun::query()
            ->with('steps')
            ->find($command->runId);

        if ($run === null) {
            throw WorkflowExecutionException::runNotFound($command->runId);
        }

        if ($this->isTerminal($run->status)) {
            return new WorkflowTimeoutResultDTO(
                runId: $run->id,
                status: $run->status,
                cancelledStepsCount: 0,
                timedOut: $run->status === WorkflowRunStatus::TimedOut,
            );
        }

        if (! $this->shouldTimeout($run, $command->timeoutSeconds)) {
            return new WorkflowTimeoutResultDTO(
                runId: $run->id,
                status: $run->status,
                cancelledStepsCount: 0,
                timedOut: false,
            );
        }

        return DB::transaction(function () use ($run, $command): WorkflowTimeoutResultDTO {
            /** @var WorkflowRun $lockedRun */
            $lockedRun = WorkflowRun::query()
                ->whereKey($run->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($this->isTerminal($lockedRun->status)) {
                return new WorkflowTimeoutResultDTO(
                    runId: $lockedRun->id,
                    status: $lockedRun->status,
                    cancelledStepsCount: 0,
                    timedOut: $lockedRun->status === WorkflowRunStatus::TimedOut,
                );
            }

            $cancelledStepsCount = $this->cancelActiveSteps($lockedRun, $command->reason);
            $this->markRunTimedOut($lockedRun, $command);

            return new WorkflowTimeoutResultDTO(
                runId: $lockedRun->id,
                status: WorkflowRunStatus::TimedOut,
                cancelledStepsCount: $cancelledStepsCount,
                timedOut: true,
            );
        });
    }

    private function cancelActiveSteps(WorkflowRun $run, ?string $reason): int
    {
        $cancellationReason = [
            'message' => $reason ?? 'Workflow execution timed out.',
            'code' => 'workflow_timeout',
        ];

        $steps = WorkflowRunStep::query()
            ->where('workflow_run_id', $run->id)
            ->whereIn('status', [
                WorkflowRunStepStatus::Running,
                WorkflowRunStepStatus::Pending,
            ])
            ->orderBy('execution_order')
            ->lockForUpdate()
            ->get();

        $cancelledAt = Carbon::now();

        foreach ($steps as $step) {
            $step->forceFill([
                'status' => WorkflowRunStepStatus::Cancelled,
                'error' => $cancellationReason,
                'completed_at' => $cancelledAt,
            ])->save();
        }

        return $steps->count();
    }

    private function markRunTimedOut(WorkflowRun $run, EnforceWorkflowTimeoutDTO $command): void
    {
        $run->forceFill([
            'status' => WorkflowRunStatus::TimedOut,
            'error' => [
                'message' => $command->reason ?? 'Workflow execution timed out.',
                'code' => 'workflow_timeout',
                'timeout_seconds' => $command->timeoutSeconds,
            ],
            'completed_at' => Carbon::now(),
        ])->save();
    }

    private function isTerminal(WorkflowRunStatus $status): bool
    {
        return in_array($status, [
            WorkflowRunStatus::Success,
            WorkflowRunStatus::Failed,
            WorkflowRunStatus::Cancelled,
            WorkflowRunStatus::TimedOut,
        ], true);
    }
}
