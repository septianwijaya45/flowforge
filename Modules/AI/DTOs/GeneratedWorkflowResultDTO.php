<?php

declare(strict_types=1);

namespace Modules\AI\DTOs;

use Modules\AI\Enums\LlmProvider;

final readonly class GeneratedWorkflowResultDTO
{
    /**
     * @param  array<string, mixed>  $definition
     * @param  array<string, mixed>|null  $schedule
     */
    public function __construct(
        public array $definition,
        public ?array $schedule,
        public LlmProvider $provider,
        public int $attempts,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'definition' => $this->definition,
            'schedule' => $this->schedule,
            'provider' => $this->provider->value,
            'attempts' => $this->attempts,
        ];
    }
}
