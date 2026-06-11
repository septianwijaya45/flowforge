<?php

declare(strict_types=1);

namespace Modules\ExecutionLog\Contracts;

use Modules\ExecutionLog\DTOs\AppendExecutionLogDTO;

interface ExecutionLogWriterServiceContract
{
    public function log(AppendExecutionLogDTO $log): void;

    /**
     * @param  list<AppendExecutionLogDTO>  $logs
     */
    public function logMany(array $logs): void;

    public function flush(): int;
}
