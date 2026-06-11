<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Auth\Models\User;
use Modules\Tenant\Models\Tenant;
use Modules\Workflow\Enums\WorkflowStatus;
use Modules\Workflow\Models\Workflow;
use Modules\Workflow\Models\WorkflowVersion;
use Tests\Support\ApiTestContext;

uses(RefreshDatabase::class);

/**
 * @return array<string, mixed>
 */
function sampleWorkflowDefinition(string $entryNodeId = 'A'): array
{
    return [
        'entry_node_id' => $entryNodeId,
        'nodes' => [
            ['id' => 'A', 'type' => 'http', 'config' => ['url' => 'https://example.com']],
            ['id' => 'B', 'type' => 'delay', 'config' => ['seconds' => 1]],
        ],
        'edges' => [
            ['id' => 'e1', 'source' => 'A', 'target' => 'B'],
        ],
    ];
}

/**
 * @return array{tenant: Tenant, workflow: Workflow, headers: array<string, string>}
 */
function versioningApiContext(): array
{
    $tenant = Tenant::query()->create([
        'name' => 'Versioning Tenant',
        'slug' => 'versioning-tenant-'.uniqid(),
        'is_active' => true,
    ]);

    $workflow = Workflow::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Versioned Workflow',
        'slug' => 'versioned-workflow-'.uniqid(),
        'status' => WorkflowStatus::Draft,
    ]);

    return [
        'tenant' => $tenant,
        'workflow' => $workflow,
        'headers' => ApiTestContext::headers($tenant),
    ];
}

it('stores every workflow definition change as a new version', function (): void {
    $context = versioningApiContext();

    $firstResponse = $this->postJson(
        "/api/v1/workflows/{$context['workflow']->id}/versions",
        [
            'definition' => sampleWorkflowDefinition(),
            'change_summary' => 'Initial version',
        ],
        $context['headers'],
    );

    $secondResponse = $this->postJson(
        "/api/v1/workflows/{$context['workflow']->id}/versions",
        [
            'definition' => [
                'entry_node_id' => 'B',
                'nodes' => [
                    ['id' => 'B', 'type' => 'delay', 'config' => ['seconds' => 2]],
                    ['id' => 'C', 'type' => 'script', 'config' => []],
                ],
                'edges' => [
                    ['id' => 'e1', 'source' => 'B', 'target' => 'C'],
                ],
            ],
            'change_summary' => 'Updated graph',
        ],
        $context['headers'],
    );

    $firstResponse->assertCreated()
        ->assertJsonPath('data.version.version_number', 1);

    $secondResponse->assertCreated()
        ->assertJsonPath('data.version.version_number', 2);

    expect(WorkflowVersion::query()->where('workflow_id', $context['workflow']->id)->count())->toBe(2);

    $context['workflow']->refresh();

    expect($context['workflow']->current_version_id)->toBe($secondResponse->json('data.version.id'));
});

it('returns paginated workflow version history', function (): void {
    $context = versioningApiContext();

    foreach (range(1, 3) as $number) {
        WorkflowVersion::query()->create([
            'tenant_id' => $context['tenant']->id,
            'workflow_id' => $context['workflow']->id,
            'version_number' => $number,
            'definition' => sampleWorkflowDefinition(),
            'definition_hash' => hash('sha256', (string) $number),
            'change_summary' => "Version {$number}",
        ]);
    }

    $response = $this->getJson(
        "/api/v1/workflows/{$context['workflow']->id}/versions?per_page=2&page=2",
        $context['headers'],
    );

    $response->assertSuccessful()
        ->assertJsonPath('data.pagination.current_page', 2)
        ->assertJsonPath('data.pagination.total', 3)
        ->assertJsonCount(1, 'data.versions')
        ->assertJsonMissingPath('data.versions.0.definition');
});

it('rolls back by creating a new version from a prior snapshot', function (): void {
    $context = versioningApiContext();

    $versionOne = WorkflowVersion::query()->create([
        'tenant_id' => $context['tenant']->id,
        'workflow_id' => $context['workflow']->id,
        'version_number' => 1,
        'definition' => sampleWorkflowDefinition('A'),
        'definition_hash' => hash('sha256', 'v1'),
        'change_summary' => 'Version 1',
    ]);

    WorkflowVersion::query()->create([
        'tenant_id' => $context['tenant']->id,
        'workflow_id' => $context['workflow']->id,
        'version_number' => 2,
        'definition' => sampleWorkflowDefinition('B'),
        'definition_hash' => hash('sha256', 'v2'),
        'change_summary' => 'Version 2',
    ]);

    $response = $this->postJson(
        "/api/v1/workflows/{$context['workflow']->id}/versions/{$versionOne->id}/rollback",
        ['change_summary' => 'Restore version 1'],
        $context['headers'],
    );

    $response->assertSuccessful()
        ->assertJsonPath('data.version.version_number', 3)
        ->assertJsonPath('data.version.definition.entry_node_id', 'A')
        ->assertJsonPath('data.version.change_summary', 'Restore version 1');

    expect(WorkflowVersion::query()->where('workflow_id', $context['workflow']->id)->count())->toBe(3);

    $context['workflow']->refresh();

    expect($context['workflow']->current_version_id)->toBe($response->json('data.version.id'));
});

it('sets the rolled back version as current and preserves version history', function (): void {
    $context = versioningApiContext();

    $versionOne = WorkflowVersion::query()->create([
        'tenant_id' => $context['tenant']->id,
        'workflow_id' => $context['workflow']->id,
        'version_number' => 1,
        'definition' => sampleWorkflowDefinition('A'),
        'definition_hash' => hash('sha256', 'rollback-v1'),
        'change_summary' => 'Version 1',
    ]);

    $versionTwo = WorkflowVersion::query()->create([
        'tenant_id' => $context['tenant']->id,
        'workflow_id' => $context['workflow']->id,
        'version_number' => 2,
        'definition' => sampleWorkflowDefinition('B'),
        'definition_hash' => hash('sha256', 'rollback-v2'),
        'change_summary' => 'Version 2',
    ]);

    $context['workflow']->update(['current_version_id' => $versionTwo->id]);

    $rollbackResponse = $this->postJson(
        "/api/v1/workflows/{$context['workflow']->id}/versions/{$versionOne->id}/rollback",
        ['change_summary' => 'Rollback to v1'],
        $context['headers'],
    );

    $rolledBackId = $rollbackResponse->json('data.version.id');

    $historyResponse = $this->getJson(
        "/api/v1/workflows/{$context['workflow']->id}/versions",
        $context['headers'],
    );

    $historyResponse->assertSuccessful()
        ->assertJsonPath('data.pagination.total', 3);

    $currentResponse = $this->getJson(
        "/api/v1/workflows/{$context['workflow']->id}/versions/current",
        $context['headers'],
    );

    $currentResponse->assertSuccessful()
        ->assertJsonPath('data.version.id', $rolledBackId)
        ->assertJsonPath('data.version.definition.entry_node_id', 'A');

    $context['workflow']->refresh();

    expect($context['workflow']->current_version_id)->toBe($rolledBackId);
});

it('rejects invalid workflow definitions', function (): void {
    $context = versioningApiContext();

    $response = $this->postJson(
        "/api/v1/workflows/{$context['workflow']->id}/versions",
        [
            'definition' => ['nodes' => []],
            'change_summary' => 'Invalid',
        ],
        $context['headers'],
    );

    $response->assertUnprocessable();
});

it('isolates version history by tenant', function (): void {
    $tenantA = Tenant::query()->create([
        'name' => 'Tenant A',
        'slug' => 'tenant-a-'.uniqid(),
        'is_active' => true,
    ]);

    $tenantB = Tenant::query()->create([
        'name' => 'Tenant B',
        'slug' => 'tenant-b-'.uniqid(),
        'is_active' => true,
    ]);

    $workflowB = Workflow::query()->create([
        'tenant_id' => $tenantB->id,
        'name' => 'Tenant B Workflow',
        'slug' => 'tenant-b-workflow',
        'status' => WorkflowStatus::Draft,
    ]);

    $user = User::factory()->create([
        'email' => 'tenant-a-versioning@example.com',
        'password' => 'password',
    ]);

    $loginResponse = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response = $this->getJson(
        "/api/v1/workflows/{$workflowB->id}/versions",
        [
            'Authorization' => 'Bearer '.$loginResponse->json('data.access_token'),
            'X-Tenant-Id' => $tenantA->id,
        ],
    );

    $response->assertNotFound();
});
