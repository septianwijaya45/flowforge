<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Contracts;

use DateTimeInterface;
use Modules\WorkflowEngine\DTOs\EnforceWorkflowTimeoutDTO;
use Modules\WorkflowEngine\DTOs\WorkflowTimeoutResultDTO;
use Modules\WorkflowEngine\Models\WorkflowRun;

interface WorkflowTimeoutManagerContract
{
    public function shouldTimeout(WorkflowRun $run, int $timeoutSeconds, ?DateTimeInterface $now = null): bool;

    public function enforce(EnforceWorkflowTimeoutDTO $command): WorkflowTimeoutResultDTO;
}
