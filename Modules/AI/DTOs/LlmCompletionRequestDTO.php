<?php

declare(strict_types=1);

namespace Modules\AI\DTOs;

use Modules\AI\Enums\LlmProvider;

final readonly class LlmCompletionRequestDTO
{
    /**
     * @param  list<LlmChatMessageDTO>  $messages
     */
    public function __construct(
        public array $messages,
        public LlmProvider $provider,
    ) {}
}
