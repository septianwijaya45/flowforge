<?php

declare(strict_types=1);

namespace Modules\AI\DTOs;

final readonly class LlmChatMessageDTO
{
    public function __construct(
        public string $role,
        public string $content,
    ) {}

    /**
     * @return array{role: string, content: string}
     */
    public function toArray(): array
    {
        return [
            'role' => $this->role,
            'content' => $this->content,
        ];
    }
}
