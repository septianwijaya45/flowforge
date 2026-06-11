<?php

declare(strict_types=1);

namespace Modules\Monitoring\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Monitoring\DTOs\ListWorkflowRunsDTO;
use Modules\WorkflowEngine\Models\WorkflowRun;

interface WorkflowRunMonitorServiceContract
{
    /**
     * @return LengthAwarePaginator<int, WorkflowRun>
     */
    public function paginate(ListWorkflowRunsDTO $filters): LengthAwarePaginator;

    public function show(WorkflowRun $run): WorkflowRun;
}
