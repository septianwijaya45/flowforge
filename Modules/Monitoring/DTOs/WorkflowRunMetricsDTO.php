<?php

declare(strict_types=1);

namespace Modules\Monitoring\DTOs;

final readonly class WorkflowRunMetricsDTO
{
    /**
     * @param  array{completed: int, success: int, failed: int, cancelled: int, timed_out: int}  $totals
     * @param  list<array{date: string, runs: int, success: int, failed: int, avg_execution_time_ms: int|null}>  $daily
     */
    public function __construct(
        public int $activeRuns,
        public float $successRate,
        public float $failureRate,
        public ?float $avgExecutionTimeMs,
        public array $totals,
        public array $daily,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'active_runs' => $this->activeRuns,
            'success_rate' => round($this->successRate, 2),
            'failure_rate' => round($this->failureRate, 2),
            'avg_execution_time_ms' => $this->avgExecutionTimeMs !== null
                ? (int) round($this->avgExecutionTimeMs)
                : null,
            'totals' => $this->totals,
            'daily' => $this->daily,
        ];
    }
}
