<?php

declare(strict_types=1);

namespace Modules\Workflow\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Workflow\DTOs\CreateWorkflowDTO;
use Modules\Workflow\DTOs\ListWorkflowsDTO;
use Modules\Workflow\DTOs\UpdateWorkflowDTO;
use Modules\Workflow\Models\Workflow;

interface WorkflowServiceContract
{
    /**
     * @return LengthAwarePaginator<int, Workflow>
     */
    public function paginate(ListWorkflowsDTO $filters): LengthAwarePaginator;

    public function create(CreateWorkflowDTO $dto): Workflow;

    public function update(Workflow $workflow, UpdateWorkflowDTO $dto): Workflow;

    public function delete(Workflow $workflow): void;
}
