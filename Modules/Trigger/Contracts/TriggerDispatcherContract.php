<?php

declare(strict_types=1);

namespace Modules\Trigger\Contracts;

use Modules\Trigger\DTOs\DispatchTriggerDTO;
use Modules\Trigger\Enums\TriggerType;
use Modules\Trigger\Models\WorkflowTrigger;
use Modules\Workflow\Models\Workflow;
use Modules\WorkflowEngine\Enums\WorkflowTriggerType;
use Modules\WorkflowEngine\Models\WorkflowRun;

interface TriggerDispatcherContract
{
    public function dispatch(
        Workflow $workflow,
        WorkflowTriggerType $runTriggerType,
        DispatchTriggerDTO $dto,
    ): WorkflowRun;

    public function dispatchFromTrigger(WorkflowTrigger $trigger, DispatchTriggerDTO $dto): WorkflowRun;

    public function mapTriggerType(TriggerType $type): WorkflowTriggerType;
}
