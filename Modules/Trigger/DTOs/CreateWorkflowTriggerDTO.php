<?php

declare(strict_types=1);

namespace Modules\Trigger\DTOs;

use Modules\Trigger\Enums\TriggerType;

final readonly class CreateWorkflowTriggerDTO
{
    /**
     * @param  array<string, mixed>|null  $config
     */
    public function __construct(
        public TriggerType $type,
        public string $name,
        public ?array $config,
        public bool $isActive,
        public ?int $createdBy,
    ) {}
}
