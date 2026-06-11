<?php

declare(strict_types=1);

namespace Modules\ExecutionLog\Services;

use Illuminate\Support\Carbon;
use Modules\ExecutionLog\Contracts\ExecutionLogRepositoryContract;
use Modules\ExecutionLog\Contracts\ExecutionLogRetentionServiceContract;
use Modules\ExecutionLog\DTOs\PurgeExpiredLogsResultDTO;

/**
 * Purges execution logs that exceed the configured retention window.
 */
class ExecutionLogRetentionService implements ExecutionLogRetentionServiceContract
{
    public function __construct(
        private readonly ExecutionLogRepositoryContract $repository,
        private readonly int $retentionDays,
        private readonly int $purgeBatchSize,
    ) {}

    public function retentionDays(): int
    {
        return $this->retentionDays;
    }

    public function purgeExpired(?int $retentionDays = null): PurgeExpiredLogsResultDTO
    {
        $days = $retentionDays ?? $this->retentionDays;
        $cutoff = Carbon::now()->subDays($days);
        $deletedCount = 0;
        $batchesProcessed = 0;

        do {
            $deletedInBatch = $this->repository->deleteOlderThan($cutoff, $this->purgeBatchSize);
            $deletedCount += $deletedInBatch;
            $batchesProcessed++;

            if ($deletedInBatch === 0) {
                break;
            }
        } while ($deletedInBatch === $this->purgeBatchSize);

        return new PurgeExpiredLogsResultDTO(
            deletedCount: $deletedCount,
            retentionDays: $days,
            cutoff: $cutoff,
            batchesProcessed: $batchesProcessed,
        );
    }
}
