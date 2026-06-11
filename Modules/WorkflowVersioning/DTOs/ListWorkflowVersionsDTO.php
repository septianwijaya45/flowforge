<?php

declare(strict_types=1);

namespace Modules\WorkflowVersioning\DTOs;

final readonly class ListWorkflowVersionsDTO
{
    public function __construct(
        public int $page,
        public int $perPage,
    ) {}
}
