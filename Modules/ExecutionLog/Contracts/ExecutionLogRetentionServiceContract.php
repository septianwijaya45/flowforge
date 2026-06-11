<?php

declare(strict_types=1);

namespace Modules\ExecutionLog\Contracts;

use Modules\ExecutionLog\DTOs\PurgeExpiredLogsResultDTO;

interface ExecutionLogRetentionServiceContract
{
    public function retentionDays(): int;

    public function purgeExpired(?int $retentionDays = null): PurgeExpiredLogsResultDTO;
}
