<?php

declare(strict_types=1);

use Modules\AI\Exceptions\LlmResponseParseException;
use Modules\AI\Services\WorkflowDefinitionSanitizer;

function workflowDefinitionSanitizer(): WorkflowDefinitionSanitizer
{
    return new WorkflowDefinitionSanitizer;
}

/**
 * @return array<string, mixed>
 */
function llmWorkflowPayload(): array
{
    return [
        'entry_node_id' => 'Check Website',
        'nodes' => [
            [
                'id' => 'check_website',
                'type' => 'http',
                'config' => [
                    'label' => 'Check website',
                    'url' => 'https://example.com',
                    'method' => 'GET',
                ],
            ],
            [
                'id' => 'status_is_500',
                'type' => 'condition',
                'config' => [
                    'operator' => 'equals',
                    'path' => 'check_website.status',
                    'value' => 500,
                ],
            ],
            ['id' => 'send_email', 'type' => 'http', 'config' => ['url' => 'https://api.example.com/email', 'method' => 'POST']],
            ['id' => 'no_op', 'type' => 'script', 'config' => ['label' => 'No action']],
            ['id' => 'invalid', 'type' => 'email', 'config' => []],
        ],
        'edges' => [
            ['source' => 'check_website', 'target' => 'status_is_500'],
            ['id' => 'e2', 'source' => 'status_is_500', 'target' => 'send_email', 'source_handle' => 'true'],
            ['id' => 'e3', 'source' => 'status_is_500', 'target' => 'no_op', 'source_handle' => 'false'],
        ],
        'schedule' => [
            'cron' => '0 * * * *',
            'description' => 'Every hour',
        ],
        'explanation' => 'should be removed',
    ];
}

describe('WorkflowDefinitionSanitizer', function (): void {
    it('normalizes ids, strips unsupported nodes, and preserves schedule metadata', function (): void {
        $result = workflowDefinitionSanitizer()->sanitize(llmWorkflowPayload());

        expect($result['definition']['entry_node_id'])->toBe('check_website')
            ->and($result['definition']['nodes'])->toHaveCount(4)
            ->and($result['definition']['edges'][0]['id'])->toBe('e1')
            ->and($result['schedule'])->toBe([
                'cron' => '0 * * * *',
                'description' => 'Every hour',
            ]);
    });

    it('rejects payloads without valid nodes', function (): void {
        expect(fn () => workflowDefinitionSanitizer()->sanitize([
            'entry_node_id' => 'start',
            'nodes' => [['id' => 'start', 'type' => 'email', 'config' => []]],
            'edges' => [],
        ]))->toThrow(LlmResponseParseException::class);
    });
});
