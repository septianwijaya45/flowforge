<?php

declare(strict_types=1);

namespace Modules\Workflow\DTOs;

use Modules\Workflow\Enums\WorkflowStatus;

final readonly class ListWorkflowsDTO
{
    public function __construct(
        public int $page,
        public int $perPage,
        public ?WorkflowStatus $status,
        public ?string $search,
        public string $sort,
        public string $direction,
    ) {}
}
