<?php

declare(strict_types=1);

namespace Modules\WorkflowVersioning\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Workflow\Models\Workflow;
use Modules\Workflow\Models\WorkflowVersion;
use Modules\WorkflowVersioning\DTOs\CreateWorkflowVersionDTO;
use Modules\WorkflowVersioning\DTOs\ListWorkflowVersionsDTO;
use Modules\WorkflowVersioning\DTOs\RollbackWorkflowVersionDTO;

interface WorkflowVersioningServiceContract
{
    /**
     * @return LengthAwarePaginator<int, WorkflowVersion>
     */
    public function history(Workflow $workflow, ListWorkflowVersionsDTO $filters): LengthAwarePaginator;

    public function createVersion(Workflow $workflow, CreateWorkflowVersionDTO $dto): WorkflowVersion;

    public function rollback(
        Workflow $workflow,
        WorkflowVersion $targetVersion,
        RollbackWorkflowVersionDTO $dto,
    ): WorkflowVersion;
}
