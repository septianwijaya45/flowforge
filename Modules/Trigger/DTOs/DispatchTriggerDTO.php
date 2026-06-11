<?php

declare(strict_types=1);

namespace Modules\Trigger\DTOs;

final readonly class DispatchTriggerDTO
{
    /**
     * @param  array<string, mixed>|null  $input
     * @param  array<string, mixed>|null  $payload
     */
    public function __construct(
        public ?array $input = null,
        public ?array $payload = null,
        public ?int $triggeredBy = null,
        public ?string $triggerId = null,
    ) {}
}
