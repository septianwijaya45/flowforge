<?php

declare(strict_types=1);

namespace Modules\Workflow\DTOs;

use Modules\Workflow\Enums\WorkflowStatus;

final readonly class CreateWorkflowDTO
{
    public function __construct(
        public string $name,
        public ?string $slug,
        public ?string $description,
        public WorkflowStatus $status,
        public ?int $createdBy,
    ) {}
}
