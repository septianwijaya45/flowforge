<?php

declare(strict_types=1);

namespace Modules\Monitoring\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Monitoring\Contracts\WorkflowRunMonitorServiceContract;
use Modules\Monitoring\DTOs\ListWorkflowRunsDTO;
use Modules\WorkflowEngine\Enums\WorkflowRunStatus;
use Modules\WorkflowEngine\Models\WorkflowRun;

class WorkflowRunMonitorService implements WorkflowRunMonitorServiceContract
{
    /**
     * @return LengthAwarePaginator<int, WorkflowRun>
     */
    public function paginate(ListWorkflowRunsDTO $filters): LengthAwarePaginator
    {
        $query = WorkflowRun::query()
            ->with(['workflow'])
            ->orderByDesc('created_at');

        if ($filters->status !== null) {
            $query->where('status', $filters->status);
        }

        if ($filters->activeOnly) {
            $query->whereIn('status', [
                WorkflowRunStatus::Pending,
                WorkflowRunStatus::Running,
            ]);
        }

        return $query->paginate(
            perPage: $filters->perPage,
            page: $filters->page,
        );
    }

    public function show(WorkflowRun $run): WorkflowRun
    {
        return $run->load(['workflow', 'steps']);
    }
}
