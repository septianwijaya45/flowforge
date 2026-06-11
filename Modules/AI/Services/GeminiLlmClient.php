<?php

declare(strict_types=1);

namespace Modules\AI\Services;

use Illuminate\Support\Facades\Http;
use Modules\AI\Contracts\LlmClientContract;
use Modules\AI\DTOs\LlmChatMessageDTO;
use Modules\AI\DTOs\LlmCompletionRequestDTO;
use Modules\AI\Exceptions\LlmClientException;

final class GeminiLlmClient implements LlmClientContract
{
    public function complete(LlmCompletionRequestDTO $request): string
    {
        $apiKey = (string) config('ai.gemini.api_key');

        if ($apiKey === '') {
            throw LlmClientException::missingApiKey('gemini');
        }

        $model = (string) config('ai.gemini.model', 'gemini-2.0-flash');
        $response = Http::acceptJson()
            ->timeout(60)
            ->withQueryParameters(['key' => $apiKey])
            ->post($this->endpoint("/models/{$model}:generateContent"), [
                'systemInstruction' => [
                    'parts' => [
                        ['text' => $this->systemInstruction($request->messages)],
                    ],
                ],
                'contents' => $this->toGeminiContents($request->messages),
                'generationConfig' => [
                    'temperature' => (float) config('ai.gemini.temperature', 0.2),
                ],
            ]);

        if (! $response->successful()) {
            throw LlmClientException::requestFailed(
                'gemini',
                (string) ($response->json('error.message') ?? $response->body()),
            );
        }

        $content = $response->json('candidates.0.content.parts.0.text');

        if (! is_string($content) || trim($content) === '') {
            throw LlmClientException::requestFailed('gemini', 'Response did not contain generated text.');
        }

        return $content;
    }

    /**
     * @param  list<LlmChatMessageDTO>  $messages
     * @return list<array{role: string, parts: list<array{text: string}>}>
     */
    private function toGeminiContents(array $messages): array
    {
        $contents = [];

        foreach ($messages as $message) {
            if ($message->role === 'system') {
                continue;
            }

            $contents[] = [
                'role' => $message->role === 'assistant' ? 'model' : 'user',
                'parts' => [
                    ['text' => $message->content],
                ],
            ];
        }

        return $contents;
    }

    /**
     * @param  list<LlmChatMessageDTO>  $messages
     */
    private function systemInstruction(array $messages): string
    {
        foreach ($messages as $message) {
            if ($message->role === 'system') {
                return $message->content;
            }
        }

        return '';
    }

    private function endpoint(string $path): string
    {
        return rtrim((string) config('ai.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta'), '/').$path;
    }
}
