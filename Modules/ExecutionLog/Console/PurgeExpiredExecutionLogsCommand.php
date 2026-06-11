<?php

declare(strict_types=1);

namespace Modules\ExecutionLog\Console;

use Illuminate\Console\Command;
use Modules\ExecutionLog\Contracts\ExecutionLogRetentionServiceContract;

class PurgeExpiredExecutionLogsCommand extends Command
{
    protected $signature = 'execution-log:purge {--days= : Override configured retention days}';

    protected $description = 'Purge execution logs older than the retention window';

    public function handle(ExecutionLogRetentionServiceContract $retentionService): int
    {
        $retentionDays = $this->option('days') !== null
            ? (int) $this->option('days')
            : $retentionService->retentionDays();

        $this->info("Purging execution logs older than {$retentionDays} day(s)...");

        $result = $retentionService->purgeExpired($retentionDays);

        $this->info(sprintf(
            'Deleted %d log(s) in %d batch(es) (cutoff: %s).',
            $result->deletedCount,
            $result->batchesProcessed,
            $result->cutoff->format('Y-m-d H:i:s'),
        ));

        return self::SUCCESS;
    }
}
