<?php

declare(strict_types=1);

namespace Modules\Trigger\DTOs;

final readonly class UpdateWorkflowTriggerDTO
{
    /**
     * @param  array<string, mixed>|null  $config
     */
    public function __construct(
        public ?string $name,
        public ?array $config,
        public ?bool $isActive,
    ) {}
}
