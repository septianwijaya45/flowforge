<?php

declare(strict_types=1);

namespace Modules\AI\Exceptions;

use RuntimeException;

final class LlmResponseParseException extends RuntimeException
{
    public static function emptyResponse(): self
    {
        return new self('LLM response was empty.');
    }

    public static function invalidJson(string $reason): self
    {
        return new self("LLM response is not valid JSON: {$reason}");
    }

    public static function invalidStructure(string $reason): self
    {
        return new self("LLM response JSON has an invalid workflow structure: {$reason}");
    }
}
