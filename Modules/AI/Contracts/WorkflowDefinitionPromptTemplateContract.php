<?php

declare(strict_types=1);

namespace Modules\AI\Contracts;

use Modules\AI\DTOs\LlmChatMessageDTO;

interface WorkflowDefinitionPromptTemplateContract
{
    /**
     * @return list<LlmChatMessageDTO>
     */
    public function initialMessages(string $userPrompt): array;

    public function correctionMessage(string $errorMessage): LlmChatMessageDTO;
}
