<?php

declare(strict_types=1);

namespace Modules\Trigger\Contracts;

use DateTimeInterface;
use Modules\Trigger\DTOs\ProcessCronTriggersResultDTO;
use Modules\Trigger\Models\WorkflowTrigger;

interface CronTriggerServiceContract
{
    public function isDue(WorkflowTrigger $trigger, DateTimeInterface $moment): bool;

    public function processDueTriggers(?DateTimeInterface $moment = null): ProcessCronTriggersResultDTO;
}
