<?php

declare(strict_types=1);

namespace Modules\AI\Services;

use Illuminate\Support\Facades\Http;
use Modules\AI\Contracts\LlmClientContract;
use Modules\AI\DTOs\LlmCompletionRequestDTO;
use Modules\AI\Exceptions\LlmClientException;

final class OpenAiLlmClient implements LlmClientContract
{
    public function complete(LlmCompletionRequestDTO $request): string
    {
        $apiKey = (string) config('ai.openai.api_key');

        if ($apiKey === '') {
            throw LlmClientException::missingApiKey('openai');
        }

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->timeout(60)
            ->post($this->endpoint('/chat/completions'), [
                'model' => (string) config('ai.openai.model', 'gpt-4o-mini'),
                'temperature' => (float) config('ai.openai.temperature', 0.2),
                'messages' => array_map(
                    static fn ($message) => $message->toArray(),
                    $request->messages,
                ),
            ]);

        if (! $response->successful()) {
            throw LlmClientException::requestFailed(
                'openai',
                (string) ($response->json('error.message') ?? $response->body()),
            );
        }

        $content = $response->json('choices.0.message.content');

        if (! is_string($content) || trim($content) === '') {
            throw LlmClientException::requestFailed('openai', 'Response did not contain message content.');
        }

        return $content;
    }

    private function endpoint(string $path): string
    {
        return rtrim((string) config('ai.openai.base_url', 'https://api.openai.com/v1'), '/').$path;
    }
}
