<?php

declare(strict_types=1);

namespace Modules\WorkflowVersioning\DTOs;

final readonly class CreateWorkflowVersionDTO
{
    /**
     * @param  array<string, mixed>  $definition
     */
    public function __construct(
        public array $definition,
        public ?string $changeSummary,
        public ?int $createdBy,
    ) {}
}
