<?php

declare(strict_types=1);

namespace Modules\Trigger\Services;

use Modules\Trigger\Contracts\ManualTriggerServiceContract;
use Modules\Trigger\Contracts\TriggerDispatcherContract;
use Modules\Trigger\DTOs\DispatchTriggerDTO;
use Modules\Trigger\Enums\TriggerType;
use Modules\Trigger\Exceptions\TriggerException;
use Modules\Trigger\Models\WorkflowTrigger;
use Modules\Workflow\Models\Workflow;
use Modules\WorkflowEngine\Enums\WorkflowTriggerType;
use Modules\WorkflowEngine\Models\WorkflowRun;

class ManualTriggerService implements ManualTriggerServiceContract
{
    public function __construct(
        private readonly TriggerDispatcherContract $dispatcher,
    ) {}

    public function fire(Workflow $workflow, DispatchTriggerDTO $dto, ?WorkflowTrigger $trigger = null): WorkflowRun
    {
        if ($trigger !== null) {
            if ($trigger->type !== TriggerType::Manual) {
                throw TriggerException::invalidTriggerType(TriggerType::Manual->value, $trigger->type->value);
            }

            if ($trigger->workflow_id !== $workflow->id) {
                throw TriggerException::invalidTriggerType(TriggerType::Manual->value, $trigger->type->value);
            }

            return $this->dispatcher->dispatchFromTrigger($trigger, $dto);
        }

        return $this->dispatcher->dispatch(
            $workflow,
            WorkflowTriggerType::Manual,
            $dto,
        );
    }
}
