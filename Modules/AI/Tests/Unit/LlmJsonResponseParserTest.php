<?php

declare(strict_types=1);

use Modules\AI\Exceptions\LlmResponseParseException;
use Modules\AI\Services\LlmJsonResponseParser;

function llmJsonResponseParser(): LlmJsonResponseParser
{
    return new LlmJsonResponseParser;
}

describe('LlmJsonResponseParser', function (): void {
    it('parses raw JSON objects', function (): void {
        $parsed = llmJsonResponseParser()->parse('{"entry_node_id":"start","nodes":[],"edges":[]}');

        expect($parsed)->toBe([
            'entry_node_id' => 'start',
            'nodes' => [],
            'edges' => [],
        ]);
    });

    it('extracts JSON from markdown fences', function (): void {
        $parsed = llmJsonResponseParser()->parse(<<<'JSON'
Here is the workflow:
```json
{"entry_node_id":"start","nodes":[],"edges":[]}
```
JSON);

        expect($parsed['entry_node_id'])->toBe('start');
    });

    it('rejects empty responses', function (): void {
        expect(fn () => llmJsonResponseParser()->parse('   '))
            ->toThrow(LlmResponseParseException::class, 'empty');
    });

    it('rejects invalid JSON', function (): void {
        expect(fn () => llmJsonResponseParser()->parse('{not-json'))
            ->toThrow(LlmResponseParseException::class, 'valid JSON');
    });
});
