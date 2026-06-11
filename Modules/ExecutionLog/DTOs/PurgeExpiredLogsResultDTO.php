<?php

declare(strict_types=1);

namespace Modules\ExecutionLog\DTOs;

use DateTimeInterface;

final readonly class PurgeExpiredLogsResultDTO
{
    public function __construct(
        public int $deletedCount,
        public int $retentionDays,
        public DateTimeInterface $cutoff,
        public int $batchesProcessed,
    ) {}
}
