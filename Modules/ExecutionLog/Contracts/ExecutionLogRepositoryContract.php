<?php

declare(strict_types=1);

namespace Modules\ExecutionLog\Contracts;

use DateTimeInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Modules\ExecutionLog\DTOs\AppendExecutionLogDTO;
use Modules\ExecutionLog\Models\ExecutionLog;

interface ExecutionLogRepositoryContract
{
    /**
     * @param  list<AppendExecutionLogDTO>  $logs
     */
    public function bulkInsert(array $logs): int;

    /**
     * @return Collection<int, ExecutionLog>
     */
    public function forRun(string $workflowRunId, ?int $limit = null): Collection;

    /**
     * @return Collection<int, ExecutionLog>
     */
    public function forTenant(string $tenantId, ?DateTimeInterface $since = null, ?int $limit = null): Collection;

    public function deleteOlderThan(Carbon $cutoff, int $batchSize): int;
}
