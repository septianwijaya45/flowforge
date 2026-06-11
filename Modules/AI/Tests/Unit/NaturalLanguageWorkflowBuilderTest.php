<?php

declare(strict_types=1);

use Modules\AI\DTOs\BuildWorkflowFromPromptDTO;
use Modules\AI\Enums\LlmProvider;
use Modules\AI\Exceptions\WorkflowGenerationException;
use Modules\AI\Services\LlmClientFactory;
use Modules\AI\Services\LlmJsonResponseParser;
use Modules\AI\Services\NaturalLanguageWorkflowBuilder;
use Modules\AI\Services\WorkflowDefinitionAiValidator;
use Modules\AI\Services\WorkflowDefinitionPromptTemplate;
use Modules\AI\Services\WorkflowDefinitionSanitizer;
use Modules\AI\Tests\Support\FakeLlmClient;
use Modules\WorkflowEngine\Services\WorkflowGraphValidator;

/**
 * @return array<string, mixed>
 */
function websiteMonitorWorkflowPayload(): array
{
    return [
        'entry_node_id' => 'check_website',
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
                    'label' => 'Status is 500',
                    'operator' => 'equals',
                    'path' => 'check_website.status',
                    'value' => 500,
                ],
            ],
            [
                'id' => 'send_email',
                'type' => 'http',
                'config' => [
                    'label' => 'Send alert email',
                    'url' => 'https://api.example.com/notifications/email',
                    'method' => 'POST',
                    'body' => [
                        'subject' => 'Website returned HTTP 500',
                    ],
                ],
            ],
            [
                'id' => 'no_op',
                'type' => 'script',
                'config' => ['label' => 'No action needed'],
            ],
        ],
        'edges' => [
            ['id' => 'e1', 'source' => 'check_website', 'target' => 'status_is_500'],
            ['id' => 'e2', 'source' => 'status_is_500', 'target' => 'send_email', 'source_handle' => 'true'],
            ['id' => 'e3', 'source' => 'status_is_500', 'target' => 'no_op', 'source_handle' => 'false'],
        ],
        'schedule' => [
            'cron' => '0 * * * *',
            'description' => 'Every hour',
        ],
    ];
}

function naturalLanguageWorkflowBuilder(FakeLlmClient $fakeClient): NaturalLanguageWorkflowBuilder
{
    $factory = new class($fakeClient) extends LlmClientFactory
    {
        public function __construct(private readonly FakeLlmClient $fakeClient)
        {
            parent::__construct(app());
        }

        public function make(?LlmProvider $provider = null): FakeLlmClient
        {
            return $this->fakeClient;
        }
    };

    return new NaturalLanguageWorkflowBuilder(
        promptTemplate: new WorkflowDefinitionPromptTemplate,
        llmClientFactory: $factory,
        responseParser: new LlmJsonResponseParser,
        sanitizer: new WorkflowDefinitionSanitizer,
        validator: new WorkflowDefinitionAiValidator(new WorkflowGraphValidator),
    );
}

describe('NaturalLanguageWorkflowBuilder', function (): void {
    beforeEach(function (): void {
        config(['ai.max_retry_attempts' => 3]);
    });

    it('builds a valid workflow DAG from natural language', function (): void {
        $fakeClient = new FakeLlmClient([
            json_encode(websiteMonitorWorkflowPayload(), JSON_THROW_ON_ERROR),
        ]);

        $result = naturalLanguageWorkflowBuilder($fakeClient)->build(
            new BuildWorkflowFromPromptDTO('Every hour check website. If status code is 500 send email.'),
        );

        expect($result->definition['entry_node_id'])->toBe('check_website')
            ->and($result->definition['nodes'])->toHaveCount(4)
            ->and($result->schedule)->toMatchArray(['cron' => '0 * * * *'])
            ->and($result->attempts)->toBe(1);
    });

    it('retries malformed LLM responses before succeeding', function (): void {
        $fakeClient = new FakeLlmClient([
            'not-json',
            json_encode(websiteMonitorWorkflowPayload(), JSON_THROW_ON_ERROR),
        ]);

        $result = naturalLanguageWorkflowBuilder($fakeClient)->build(
            new BuildWorkflowFromPromptDTO('Every hour check website. If status code is 500 send email.'),
        );

        expect($result->attempts)->toBe(2)
            ->and($result->definition['entry_node_id'])->toBe('check_website');
    });

    it('fails after exhausting retry attempts', function (): void {
        $fakeClient = new FakeLlmClient([
            'still-not-json',
            '{broken',
            '[]',
        ]);

        expect(fn () => naturalLanguageWorkflowBuilder($fakeClient)->build(
            new BuildWorkflowFromPromptDTO('Every hour check website.'),
        ))->toThrow(WorkflowGenerationException::class, 'after 3 attempts');
    });
});
