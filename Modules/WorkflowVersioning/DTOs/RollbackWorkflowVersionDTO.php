<?php

declare(strict_types=1);

namespace Modules\WorkflowVersioning\DTOs;

final readonly class RollbackWorkflowVersionDTO
{
    public function __construct(
        public ?string $changeSummary,
        public ?int $createdBy,
    ) {}
}
