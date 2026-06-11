<?php

declare(strict_types=1);

namespace Modules\Scheduler\Services;

use Illuminate\Support\Collection;
use Modules\Scheduler\Contracts\ScheduleServiceContract;
use Modules\Scheduler\DTOs\CreateScheduleDTO;
use Modules\Scheduler\DTOs\UpdateScheduleDTO;
use Modules\Trigger\Contracts\WorkflowTriggerServiceContract;
use Modules\Trigger\DTOs\CreateWorkflowTriggerDTO;
use Modules\Trigger\DTOs\UpdateWorkflowTriggerDTO;
use Modules\Trigger\Enums\TriggerType;
use Modules\Trigger\Exceptions\TriggerException;
use Modules\Trigger\Models\WorkflowTrigger;
use Modules\Workflow\Models\Workflow;

class ScheduleService implements ScheduleServiceContract
{
    public function __construct(
        private readonly WorkflowTriggerServiceContract $triggerService,
    ) {}

    /**
     * @return Collection<int, WorkflowTrigger>
     */
    public function list(): Collection
    {
        return WorkflowTrigger::query()
            ->where('type', TriggerType::Cron)
            ->with(['workflow:id,name,slug'])
            ->orderBy('name')
            ->get();
    }

    public function create(CreateScheduleDTO $dto): WorkflowTrigger
    {
        $workflow = Workflow::query()->findOrFail($dto->workflowId);

        return $this->triggerService->create($workflow, new CreateWorkflowTriggerDTO(
            type: TriggerType::Cron,
            name: $dto->name,
            config: ['expression' => $dto->cronExpression],
            isActive: $dto->isActive,
            createdBy: $dto->createdBy,
        ))->load('workflow:id,name,slug');
    }

    public function update(WorkflowTrigger $schedule, UpdateScheduleDTO $dto): WorkflowTrigger
    {
        $this->assertCronSchedule($schedule);

        $config = $dto->cronExpression !== null
            ? ['expression' => $dto->cronExpression]
            : null;

        return $this->triggerService->update($schedule, new UpdateWorkflowTriggerDTO(
            name: $dto->name,
            config: $config,
            isActive: $dto->isActive,
        ))->load('workflow:id,name,slug');
    }

    public function delete(WorkflowTrigger $schedule): void
    {
        $this->assertCronSchedule($schedule);

        $this->triggerService->delete($schedule);
    }

    private function assertCronSchedule(WorkflowTrigger $schedule): void
    {
        if ($schedule->type !== TriggerType::Cron) {
            throw TriggerException::invalidTriggerType(TriggerType::Cron->value, $schedule->type->value);
        }
    }
}
