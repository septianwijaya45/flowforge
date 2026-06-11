<?php

declare(strict_types=1);

namespace Modules\AI\Enums;

enum LlmProvider: string
{
    case OpenAi = 'openai';
    case Gemini = 'gemini';

    public static function fromConfig(?string $value = null): self
    {
        $resolved = $value ?? (string) config('ai.provider', self::OpenAi->value);

        return self::tryFrom($resolved) ?? self::OpenAi;
    }
}
