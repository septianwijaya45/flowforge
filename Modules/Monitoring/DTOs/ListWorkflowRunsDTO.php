<?php

declare(strict_types=1);

namespace Modules\Monitoring\DTOs;

use Modules\WorkflowEngine\Enums\WorkflowRunStatus;

final readonly class ListWorkflowRunsDTO
{
    public function __construct(
        public int $page,
        public int $perPage,
        public ?WorkflowRunStatus $status,
        public bool $activeOnly,
    ) {}
}
