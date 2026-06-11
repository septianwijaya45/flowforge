<?php

declare(strict_types=1);

namespace Modules\ExecutionLog\Contracts;

use Illuminate\Support\Collection;
use Modules\ExecutionLog\Models\ExecutionLog;
use Modules\WorkflowEngine\Models\WorkflowRun;

interface ExecutionLogServiceContract
{
    /**
     * @return Collection<int, ExecutionLog>
     */
    public function forRun(WorkflowRun $run, ?int $limit = null): Collection;
}
