<?php

declare(strict_types=1);

namespace Modules\AI\Services;

use JsonException;
use Modules\AI\Exceptions\LlmResponseParseException;

final class LlmJsonResponseParser
{
    /**
     * @return array<string, mixed>
     */
    public function parse(string $rawResponse): array
    {
        $payload = trim($rawResponse);

        if ($payload === '') {
            throw LlmResponseParseException::emptyResponse();
        }

        $payload = $this->extractJsonPayload($payload);

        try {
            $decoded = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw LlmResponseParseException::invalidJson($exception->getMessage());
        }

        if (! is_array($decoded)) {
            throw LlmResponseParseException::invalidStructure('Root JSON value must be an object.');
        }

        return $decoded;
    }

    private function extractJsonPayload(string $payload): string
    {
        if (preg_match('/```(?:json)?\s*(\{.*\})\s*```/s', $payload, $matches) === 1) {
            return trim($matches[1]);
        }

        $start = strpos($payload, '{');
        $end = strrpos($payload, '}');

        if ($start === false || $end === false || $end <= $start) {
            return $payload;
        }

        return substr($payload, $start, $end - $start + 1);
    }
}
