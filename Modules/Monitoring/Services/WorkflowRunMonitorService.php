<?php

declare(strict_types=1);

namespace Modules\Monitoring\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Modules\Monitoring\Contracts\WorkflowRunMonitorServiceContract;
use Modules\Monitoring\DTOs\ListWorkflowRunsDTO;
use Modules\Monitoring\DTOs\WorkflowRunMetricsDTO;
use Modules\WorkflowEngine\Enums\WorkflowRunStatus;
use Modules\WorkflowEngine\Models\WorkflowRun;
use Modules\WorkflowEngine\Support\WorkflowRunQueryExpressions;

class WorkflowRunMonitorService implements WorkflowRunMonitorServiceContract
{
    /**
     * @return LengthAwarePaginator<int, WorkflowRun>
     */
    public function paginate(ListWorkflowRunsDTO $filters): LengthAwarePaginator
    {
        $query = WorkflowRun::query()
            ->select([
                'id',
                'tenant_id',
                'workflow_id',
                'workflow_version_id',
                'status',
                'trigger_type',
                'started_at',
                'completed_at',
                'created_at',
            ])
            ->with(['workflow:id,name'])
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
        return $run->load([
            'workflow:id,name,slug',
            'steps' => fn ($query) => $query
                ->select([
                    'id',
                    'workflow_run_id',
                    'node_id',
                    'node_type',
                    'node_label',
                    'status',
                    'attempt',
                    'execution_order',
                    'error',
                    'started_at',
                    'completed_at',
                    'duration_ms',
                ])
                ->orderBy('execution_order'),
        ]);
    }

    public function metrics(int $days = 30): WorkflowRunMetricsDTO
    {
        $since = Carbon::now()->subDays($days)->startOfDay();
        $expressions = WorkflowRunQueryExpressions::forConnection(
            WorkflowRun::query()->getConnection(),
        );
        $durationMs = $expressions->executionDurationMs();
        $dateExpression = $expressions->dateFromCreatedAt();

        $activeRuns = WorkflowRun::query()
            ->whereIn('status', [
                WorkflowRunStatus::Pending,
                WorkflowRunStatus::Running,
            ])
            ->count();

        $totalsRow = WorkflowRun::query()
            ->where('created_at', '>=', $since)
            ->whereIn('status', $this->terminalStatuses())
            ->selectRaw('
                COUNT(*) as completed,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as success,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cancelled,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as timed_out,
                AVG(CASE WHEN started_at IS NOT NULL AND completed_at IS NOT NULL THEN '.$durationMs.' END) as avg_execution_time_ms
            ', [
                WorkflowRunStatus::Success->value,
                WorkflowRunStatus::Failed->value,
                WorkflowRunStatus::Cancelled->value,
                WorkflowRunStatus::TimedOut->value,
            ])
            ->first();

        $completedTotal = (int) ($totalsRow->completed ?? 0);
        $successCount = (int) ($totalsRow->success ?? 0);
        $failedCount = (int) ($totalsRow->failed ?? 0);
        $cancelledCount = (int) ($totalsRow->cancelled ?? 0);
        $timedOutCount = (int) ($totalsRow->timed_out ?? 0);
        $failureTotal = $failedCount + $timedOutCount;

        $successRate = $completedTotal > 0 ? ($successCount / $completedTotal) * 100 : 0.0;
        $failureRate = $completedTotal > 0 ? ($failureTotal / $completedTotal) * 100 : 0.0;

        $avgExecutionTimeMs = $totalsRow->avg_execution_time_ms !== null
            ? (float) $totalsRow->avg_execution_time_ms
            : null;

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
            daily: $this->buildDailyMetrics($since, $days, $dateExpression, $durationMs),
        );
    }

    /**
     * @return list<WorkflowRunStatus>
     */
    private function terminalStatuses(): array
    {
        return [
            WorkflowRunStatus::Success,
            WorkflowRunStatus::Failed,
            WorkflowRunStatus::Cancelled,
            WorkflowRunStatus::TimedOut,
        ];
    }

    /**
     * @return list<array{date: string, runs: int, success: int, failed: int, avg_execution_time_ms: int|null}>
     */
    private function buildDailyMetrics(
        Carbon $since,
        int $days,
        string $dateExpression,
        string $durationMs,
    ): array {
        $dailyRows = WorkflowRun::query()
            ->where('created_at', '>=', $since)
            ->selectRaw('
                '.$dateExpression.' as date,
                COUNT(*) as runs,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as success,
                SUM(CASE WHEN status IN (?, ?) THEN 1 ELSE 0 END) as failed,
                AVG(CASE WHEN started_at IS NOT NULL AND completed_at IS NOT NULL THEN '.$durationMs.' END) as avg_execution_time_ms
            ', [
                WorkflowRunStatus::Success->value,
                WorkflowRunStatus::Failed->value,
                WorkflowRunStatus::TimedOut->value,
            ])
            ->groupByRaw($dateExpression)
            ->orderBy('date')
            ->get()
            ->keyBy(static fn ($row): string => (string) $row->date);

        $daily = [];

        for ($offset = $days - 1; $offset >= 0; $offset--) {
            $date = Carbon::now()->subDays($offset)->toDateString();
            $row = $dailyRows->get($date);

            $daily[] = [
                'date' => $date,
                'runs' => (int) ($row->runs ?? 0),
                'success' => (int) ($row->success ?? 0),
                'failed' => (int) ($row->failed ?? 0),
                'avg_execution_time_ms' => $row?->avg_execution_time_ms !== null
                    ? (int) round((float) $row->avg_execution_time_ms)
                    : null,
            ];
        }

        return $daily;
    }
}
