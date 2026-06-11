<?php

declare(strict_types=1);

namespace Modules\AI\DTOs;

use Modules\AI\Enums\LlmProvider;

final readonly class BuildWorkflowFromPromptDTO
{
    public function __construct(
        public string $prompt,
        public ?LlmProvider $provider = null,
    ) {}
}
