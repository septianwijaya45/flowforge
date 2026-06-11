<?php

declare(strict_types=1);

namespace Modules\ExecutionLog\Services;

use Modules\ExecutionLog\Contracts\ExecutionLogRepositoryContract;
use Modules\ExecutionLog\Contracts\ExecutionLogWriterServiceContract;
use Modules\ExecutionLog\DTOs\AppendExecutionLogDTO;

/**
 * Buffers execution logs and flushes them in bulk for high-volume writes.
 */
class ExecutionLogWriterService implements ExecutionLogWriterServiceContract
{
    /** @var list<AppendExecutionLogDTO> */
    private array $buffer = [];

    public function __construct(
        private readonly ExecutionLogRepositoryContract $repository,
        private readonly int $batchSize,
    ) {}

    public function log(AppendExecutionLogDTO $log): void
    {
        $this->buffer[] = $log;

        if (count($this->buffer) >= $this->batchSize) {
            $this->flush();
        }
    }

    /**
     * @param  list<AppendExecutionLogDTO>  $logs
     */
    public function logMany(array $logs): void
    {
        foreach ($logs as $log) {
            $this->log($log);
        }
    }

    public function flush(): int
    {
        if ($this->buffer === []) {
            return 0;
        }

        $written = $this->repository->bulkInsert($this->buffer);
        $this->buffer = [];

        return $written;
    }
}
