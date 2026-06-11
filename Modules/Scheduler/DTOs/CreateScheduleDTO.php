<?php

declare(strict_types=1);

namespace Modules\Scheduler\DTOs;

final readonly class CreateScheduleDTO
{
    public function __construct(
        public string $workflowId,
        public string $name,
        public string $cronExpression,
        public bool $isActive = true,
        public ?int $createdBy = null,
    ) {}
}
