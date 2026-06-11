<?php

declare(strict_types=1);

namespace Modules\Monitoring\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Modules\Monitoring\Contracts\WorkflowRunMonitorServiceContract;
use Modules\Monitoring\DTOs\ListWorkflowRunsDTO;
use Modules\Monitoring\DTOs\WorkflowRunMetricsDTO;
use Modules\WorkflowEngine\Enums\WorkflowRunStatus;
use Modules\WorkflowEngine\Models\WorkflowRun;

class WorkflowRunMonitorService implements WorkflowRunMonitorServiceContract
{
    /**
     * @return LengthAwarePaginator<int, WorkflowRun>
     */
    public function paginate(ListWorkflowRunsDTO $filters): LengthAwarePaginator
    {
        $query = WorkflowRun::query()
            ->with(['workflow'])
            ->orderByDesc('created_at');

        if ($filters->status !== null) {
            $query->where('status', $filters->status);
        }

        if ($filters->activeOnly) {
            $query->whereIn('status', [
                WorkflowRunStatus::Pending,
                WorkflowRunStatus::Running,
            ]);
        }

        return $query->paginate(
            perPage: $filters->perPage,
            page: $filters->page,
        );
    }

    public function show(WorkflowRun $run): WorkflowRun
    {
        return $run->load(['workflow', 'steps']);
    }

    public function metrics(int $days = 30): WorkflowRunMetricsDTO
    {
        $since = Carbon::now()->subDays($days)->startOfDay();

        $activeRuns = WorkflowRun::query()
            ->whereIn('status', [
                WorkflowRunStatus::Pending,
                WorkflowRunStatus::Running,
            ])
            ->count();

        $runsInPeriod = WorkflowRun::query()
            ->where('created_at', '>=', $since)
            ->get(['status', 'started_at', 'completed_at', 'created_at']);

        $terminalRuns = $runsInPeriod->filter(
            fn (WorkflowRun $run): bool => in_array($run->status, [
                WorkflowRunStatus::Success,
                WorkflowRunStatus::Failed,
                WorkflowRunStatus::Cancelled,
                WorkflowRunStatus::TimedOut,
            ], true),
        );

        $successCount = $terminalRuns->where('status', WorkflowRunStatus::Success)->count();
        $failedCount = $terminalRuns->where('status', WorkflowRunStatus::Failed)->count();
        $cancelledCount = $terminalRuns->where('status', WorkflowRunStatus::Cancelled)->count();
        $timedOutCount = $terminalRuns->where('status', WorkflowRunStatus::TimedOut)->count();
        $completedTotal = $terminalRuns->count();

        $failureTotal = $failedCount + $timedOutCount;

        $successRate = $completedTotal > 0 ? ($successCount / $completedTotal) * 100 : 0.0;
        $failureRate = $completedTotal > 0 ? ($failureTotal / $completedTotal) * 100 : 0.0;

        $avgExecutionTimeMs = $this->averageExecutionTimeMs(
            $runsInPeriod->filter(
                fn (WorkflowRun $run): bool => $run->started_at !== null && $run->completed_at !== null,
            ),
        );

        return new WorkflowRunMetricsDTO(
            activeRuns: $activeRuns,
            successRate: $successRate,
            failureRate: $failureRate,
            avgExecutionTimeMs: $avgExecutionTimeMs,
            totals: [
                'completed' => $completedTotal,
                'success' => $successCount,
                'failed' => $failedCount,
                'cancelled' => $cancelledCount,
                'timed_out' => $timedOutCount,
            ],
            daily: $this->buildDailyMetrics($runsInPeriod, $since, $days),
        );
    }

    /**
     * @param  Collection<int, WorkflowRun>  $runs
     */
    private function averageExecutionTimeMs(Collection $runs): ?float
    {
        if ($runs->isEmpty()) {
            return null;
        }

        $totalMs = $runs->sum(
            fn (WorkflowRun $run): int => (int) $run->started_at?->diffInMilliseconds($run->completed_at),
        );

        return $totalMs / $runs->count();
    }

    /**
     * @param  Collection<int, WorkflowRun>  $runs
     * @return list<array{date: string, runs: int, success: int, failed: int, avg_execution_time_ms: int|null}>
     */
    private function buildDailyMetrics(Collection $runs, Carbon $since, int $days): array
    {
        $daily = [];

        for ($offset = $days - 1; $offset >= 0; $offset--) {
            $date = Carbon::now()->subDays($offset)->toDateString();

            $dayRuns = $runs->filter(
                fn (WorkflowRun $run): bool => $run->created_at?->toDateString() === $date,
            );

            $failed = $dayRuns->filter(
                fn (WorkflowRun $run): bool => in_array($run->status, [
                    WorkflowRunStatus::Failed,
                    WorkflowRunStatus::TimedOut,
                ], true),
            )->count();

            $completedDayRuns = $dayRuns->filter(
                fn (WorkflowRun $run): bool => $run->started_at !== null && $run->completed_at !== null,
            );

            $avgMs = $this->averageExecutionTimeMs($completedDayRuns);

            $daily[] = [
                'date' => $date,
                'runs' => $dayRuns->count(),
                'success' => $dayRuns->where('status', WorkflowRunStatus::Success)->count(),
                'failed' => $failed,
                'avg_execution_time_ms' => $avgMs !== null ? (int) round($avgMs) : null,
            ];
        }

        return $daily;
    }
}
