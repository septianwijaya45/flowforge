<?php

declare(strict_types=1);

namespace Modules\ExecutionLog\Services;

use Illuminate\Support\Collection;
use Modules\ExecutionLog\Contracts\ExecutionLogRepositoryContract;
use Modules\ExecutionLog\Contracts\ExecutionLogServiceContract;
use Modules\ExecutionLog\Models\ExecutionLog;
use Modules\WorkflowEngine\Models\WorkflowRun;

class ExecutionLogService implements ExecutionLogServiceContract
{
    public function __construct(
        private readonly ExecutionLogRepositoryContract $repository,
    ) {}

    /**
     * @return Collection<int, ExecutionLog>
     */
    public function forRun(WorkflowRun $run, ?int $limit = null): Collection
    {
        return $this->repository->forRun($run->id, $limit);
    }
}
