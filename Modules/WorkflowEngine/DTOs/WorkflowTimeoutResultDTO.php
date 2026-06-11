<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\DTOs;

use Modules\WorkflowEngine\Enums\WorkflowRunStatus;

final readonly class WorkflowTimeoutResultDTO
{
    public function __construct(
        public string $runId,
        public WorkflowRunStatus $status,
        public int $cancelledStepsCount,
        public bool $timedOut,
    ) {}
}
