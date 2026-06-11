<?php

declare(strict_types=1);

namespace Modules\Scheduler\DTOs;

final readonly class UpdateScheduleDTO
{
    public function __construct(
        public ?string $name = null,
        public ?string $cronExpression = null,
        public ?bool $isActive = null,
    ) {}
}
