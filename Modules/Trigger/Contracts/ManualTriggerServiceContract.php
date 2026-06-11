<?php

declare(strict_types=1);

namespace Modules\Trigger\Contracts;

use Modules\Trigger\DTOs\DispatchTriggerDTO;
use Modules\Trigger\Models\WorkflowTrigger;
use Modules\Workflow\Models\Workflow;
use Modules\WorkflowEngine\Models\WorkflowRun;

interface ManualTriggerServiceContract
{
    public function fire(Workflow $workflow, DispatchTriggerDTO $dto, ?WorkflowTrigger $trigger = null): WorkflowRun;
}
