<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\DTOs;

final readonly class ExecuteWorkflowRunDTO
{
    public function __construct(
        public string $runId,
    ) {}
}
