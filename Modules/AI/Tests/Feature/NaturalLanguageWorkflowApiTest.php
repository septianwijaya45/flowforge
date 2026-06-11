<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\AI\Contracts\NaturalLanguageWorkflowBuilderContract;
use Modules\AI\DTOs\BuildWorkflowFromPromptDTO;
use Modules\AI\DTOs\GeneratedWorkflowResultDTO;
use Modules\AI\Enums\LlmProvider;
use Modules\Auth\Enums\UserRole;
use Modules\Tenant\Models\Tenant;
use Tests\Support\ApiTestContext;

uses(RefreshDatabase::class);

/**
 * @return array<string, mixed>
 */
function apiWebsiteMonitorWorkflowPayload(): array
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

it('returns a validated workflow definition from natural language', function (): void {
    $tenant = Tenant::query()->create([
        'name' => 'AI Tenant',
        'slug' => 'ai-tenant-'.uniqid(),
        'is_active' => true,
    ]);

    $fakeBuilder = new class implements NaturalLanguageWorkflowBuilderContract
    {
        public function build(BuildWorkflowFromPromptDTO $command): GeneratedWorkflowResultDTO
        {
            return new GeneratedWorkflowResultDTO(
                definition: [
                    'entry_node_id' => 'check_website',
                    'nodes' => apiWebsiteMonitorWorkflowPayload()['nodes'],
                    'edges' => apiWebsiteMonitorWorkflowPayload()['edges'],
                ],
                schedule: apiWebsiteMonitorWorkflowPayload()['schedule'],
                provider: LlmProvider::OpenAi,
                attempts: 1,
            );
        }
    };

    app()->instance(NaturalLanguageWorkflowBuilderContract::class, $fakeBuilder);

    $response = $this->postJson(
        '/api/v1/ai/workflows/build',
        ['prompt' => 'Every hour check website. If status code is 500 send email.'],
        ApiTestContext::headers($tenant),
    );

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.definition.entry_node_id', 'check_website')
        ->assertJsonPath('data.schedule.cron', '0 * * * *')
        ->assertJsonPath('data.provider', 'openai')
        ->assertJsonPath('data.attempts', 1);
});

it('validates prompt input', function (): void {
    $tenant = Tenant::query()->create([
        'name' => 'AI Validation Tenant',
        'slug' => 'ai-validation-tenant-'.uniqid(),
        'is_active' => true,
    ]);

    $response = $this->postJson(
        '/api/v1/ai/workflows/build',
        ['prompt' => 'no'],
        ApiTestContext::headers($tenant),
    );

    $response->assertUnprocessable();
});

it('rejects viewers from building workflows with AI', function (): void {
    $tenant = Tenant::query()->create([
        'name' => 'AI Viewer Tenant',
        'slug' => 'ai-viewer-tenant-'.uniqid(),
        'is_active' => true,
    ]);

    $response = $this->postJson(
        '/api/v1/ai/workflows/build',
        ['prompt' => 'Every hour check website.'],
        ApiTestContext::headers($tenant, role: UserRole::Viewer),
    );

    $response->assertForbidden();
});
