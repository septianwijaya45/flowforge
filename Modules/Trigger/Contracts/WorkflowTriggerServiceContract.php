<?php

declare(strict_types=1);

namespace Modules\Trigger\Contracts;

use Illuminate\Support\Collection;
use Modules\Trigger\DTOs\CreateWorkflowTriggerDTO;
use Modules\Trigger\DTOs\UpdateWorkflowTriggerDTO;
use Modules\Trigger\Models\WorkflowTrigger;
use Modules\Workflow\Models\Workflow;

interface WorkflowTriggerServiceContract
{
    /**
     * @return Collection<int, WorkflowTrigger>
     */
    public function listForWorkflow(Workflow $workflow): Collection;

    public function create(Workflow $workflow, CreateWorkflowTriggerDTO $dto): WorkflowTrigger;

    public function update(WorkflowTrigger $trigger, UpdateWorkflowTriggerDTO $dto): WorkflowTrigger;

    public function delete(WorkflowTrigger $trigger): void;
}
