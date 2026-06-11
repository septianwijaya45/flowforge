<?php

declare(strict_types=1);

namespace Modules\AI\Exceptions;

use RuntimeException;

final class LlmClientException extends RuntimeException
{
    public static function missingApiKey(string $provider): self
    {
        return new self("API key is not configured for the [{$provider}] LLM provider.");
    }

    public static function requestFailed(string $provider, string $reason): self
    {
        return new self("LLM request to [{$provider}] failed: {$reason}");
    }
}
