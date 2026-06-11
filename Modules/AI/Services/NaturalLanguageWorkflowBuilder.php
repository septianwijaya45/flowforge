<?php

declare(strict_types=1);

namespace Modules\AI\Services;

use Modules\AI\Contracts\NaturalLanguageWorkflowBuilderContract;
use Modules\AI\Contracts\WorkflowDefinitionPromptTemplateContract;
use Modules\AI\DTOs\BuildWorkflowFromPromptDTO;
use Modules\AI\DTOs\GeneratedWorkflowResultDTO;
use Modules\AI\DTOs\LlmChatMessageDTO;
use Modules\AI\DTOs\LlmCompletionRequestDTO;
use Modules\AI\Enums\LlmProvider;
use Modules\AI\Exceptions\WorkflowGenerationException;
use Throwable;

final class NaturalLanguageWorkflowBuilder implements NaturalLanguageWorkflowBuilderContract
{
    public function __construct(
        private readonly WorkflowDefinitionPromptTemplateContract $promptTemplate,
        private readonly LlmClientFactory $llmClientFactory,
        private readonly LlmJsonResponseParser $responseParser,
        private readonly WorkflowDefinitionSanitizer $sanitizer,
        private readonly WorkflowDefinitionAiValidator $validator,
    ) {}

    public function build(BuildWorkflowFromPromptDTO $command): GeneratedWorkflowResultDTO
    {
        $provider = $command->provider ?? LlmProvider::fromConfig();
        $llmClient = $this->llmClientFactory->make($provider);
        $maxAttempts = max(1, (int) config('ai.max_retry_attempts', 3));

        /** @var list<LlmChatMessageDTO> $messages */
        $messages = $this->promptTemplate->initialMessages($command->prompt);

        $lastException = null;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $rawResponse = null;

            try {
                $rawResponse = $llmClient->complete(new LlmCompletionRequestDTO(
                    messages: $messages,
                    provider: $provider,
                ));

                $parsed = $this->responseParser->parse($rawResponse);
                $sanitized = $this->sanitizer->sanitize($parsed);
                $this->validator->validate($sanitized['definition']);

                return new GeneratedWorkflowResultDTO(
                    definition: $sanitized['definition'],
                    schedule: $sanitized['schedule'],
                    provider: $provider,
                    attempts: $attempt,
                );
            } catch (Throwable $throwable) {
                $lastException = $throwable;
                $messages[] = new LlmChatMessageDTO('assistant', $this->assistantPayloadFromAttempt($throwable, $rawResponse ?? null));
                $messages[] = $this->promptTemplate->correctionMessage(
                    $this->validator->validationMessage($throwable),
                );
            }
        }

        throw WorkflowGenerationException::maxRetriesExceeded($maxAttempts, $lastException);
    }

    private function assistantPayloadFromAttempt(Throwable $throwable, ?string $rawResponse): string
    {
        if (is_string($rawResponse) && trim($rawResponse) !== '') {
            return trim($rawResponse);
        }

        return 'Previous attempt failed: '.$throwable->getMessage();
    }
}
