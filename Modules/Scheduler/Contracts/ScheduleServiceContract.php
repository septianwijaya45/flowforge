<?php

declare(strict_types=1);

namespace Modules\Scheduler\Contracts;

use Illuminate\Support\Collection;
use Modules\Scheduler\DTOs\CreateScheduleDTO;
use Modules\Scheduler\DTOs\UpdateScheduleDTO;
use Modules\Trigger\Models\WorkflowTrigger;

interface ScheduleServiceContract
{
    /**
     * @return Collection<int, WorkflowTrigger>
     */
    public function list(): Collection;

    public function create(CreateScheduleDTO $dto): WorkflowTrigger;

    public function update(WorkflowTrigger $schedule, UpdateScheduleDTO $dto): WorkflowTrigger;

    public function delete(WorkflowTrigger $schedule): void;
}
