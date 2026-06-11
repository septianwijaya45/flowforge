<?php

declare(strict_types=1);

namespace Modules\AI\Services;

use Illuminate\Contracts\Foundation\Application;
use Modules\AI\Contracts\LlmClientContract;
use Modules\AI\Enums\LlmProvider;

class LlmClientFactory
{
    public function __construct(
        private readonly Application $app,
    ) {}

    public function make(?LlmProvider $provider = null): LlmClientContract
    {
        $provider ??= LlmProvider::fromConfig();

        return match ($provider) {
            LlmProvider::OpenAi => $this->app->make(OpenAiLlmClient::class),
            LlmProvider::Gemini => $this->app->make(GeminiLlmClient::class),
        };
    }
}
