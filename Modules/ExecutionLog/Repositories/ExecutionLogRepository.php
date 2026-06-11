<?php

declare(strict_types=1);

namespace Modules\ExecutionLog\Repositories;

use DateTimeInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\ExecutionLog\Contracts\ExecutionLogRepositoryContract;
use Modules\ExecutionLog\DTOs\AppendExecutionLogDTO;
use Modules\ExecutionLog\Models\ExecutionLog;

class ExecutionLogRepository implements ExecutionLogRepositoryContract
{
    /**
     * @param  list<AppendExecutionLogDTO>  $logs
     */
    public function bulkInsert(array $logs): int
    {
        if ($logs === []) {
            return 0;
        }

        $now = Carbon::now()->toDateTimeString();
        $rows = [];

        foreach ($logs as $log) {
            $loggedAt = $log->loggedAt !== null
                ? Carbon::instance($log->loggedAt)->toDateTimeString()
                : $now;

            $rows[] = [
                'id' => (string) Str::uuid(),
                'tenant_id' => $log->tenantId,
                'workflow_id' => $log->workflowId,
                'workflow_run_id' => $log->workflowRunId,
                'workflow_run_step_id' => $log->workflowRunStepId,
                'node_id' => $log->nodeId,
                'level' => $log->level->value,
                'message' => $log->message,
                'context' => $log->context !== null ? json_encode($log->context) : null,
                'logged_at' => $loggedAt,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $connection = ExecutionLog::query()->getConnection();

        foreach (array_chunk($rows, 500) as $chunk) {
            $connection->table('execution_logs')->insert($chunk);
        }

        return count($rows);
    }

    /**
     * @return Collection<int, ExecutionLog>
     */
    public function forRun(string $workflowRunId, ?int $limit = null): Collection
    {
        $query = ExecutionLog::query()
            ->forRun($workflowRunId)
            ->orderBy('logged_at');

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * @return Collection<int, ExecutionLog>
     */
    public function forTenant(string $tenantId, ?DateTimeInterface $since = null, ?int $limit = null): Collection
    {
        $query = ExecutionLog::query()
            ->forTenant($tenantId)
            ->orderByDesc('logged_at');

        if ($since !== null) {
            $query->where('logged_at', '>=', Carbon::instance($since));
        }

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->get();
    }

    public function deleteOlderThan(Carbon $cutoff, int $batchSize): int
    {
        $ids = ExecutionLog::query()
            ->loggedBefore($cutoff)
            ->orderBy('logged_at')
            ->limit($batchSize)
            ->pluck('id');

        if ($ids->isEmpty()) {
            return 0;
        }

        return ExecutionLog::query()->whereIn('id', $ids)->delete();
    }
}
