<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Auth\Models\User;
use Modules\Tenant\Models\Tenant;
use Modules\Workflow\Enums\WorkflowStatus;
use Modules\Workflow\Models\Workflow;

uses(RefreshDatabase::class);

/**
 * @return array<string, mixed>
 */
function currentVersionSampleDefinition(string $entryNodeId = 'A'): array
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
function currentVersionApiContext(): array
{
    $tenant = Tenant::query()->create([
        'name' => 'Current Version Tenant',
        'slug' => 'current-version-tenant-'.uniqid(),
        'is_active' => true,
    ]);

    $workflow = Workflow::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Current Version Workflow',
        'slug' => 'current-version-workflow-'.uniqid(),
        'status' => WorkflowStatus::Draft,
    ]);

    $user = User::factory()->create([
        'email' => 'current-version-'.uniqid().'@example.com',
        'password' => 'password',
    ]);

    $loginResponse = test()->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    return [
        'tenant' => $tenant,
        'workflow' => $workflow,
        'headers' => [
            'Authorization' => 'Bearer '.$loginResponse->json('data.access_token'),
            'X-Tenant-Id' => $tenant->id,
        ],
    ];
}

it('returns the current workflow version with definition', function (): void {
    $context = currentVersionApiContext();

    $this->postJson(
        "/api/v1/workflows/{$context['workflow']->id}/versions",
        ['definition' => currentVersionSampleDefinition()],
        $context['headers'],
    )->assertCreated();

    $response = $this->getJson(
        "/api/v1/workflows/{$context['workflow']->id}/versions/current",
        $context['headers'],
    );

    $response->assertSuccessful()
        ->assertJsonPath('data.version.is_current', true)
        ->assertJsonPath('data.version.definition.entry_node_id', 'A')
        ->assertJsonCount(2, 'data.version.definition.nodes');
});

it('returns not found when workflow has no versions', function (): void {
    $context = currentVersionApiContext();

    $response = $this->getJson(
        "/api/v1/workflows/{$context['workflow']->id}/versions/current",
        $context['headers'],
    );

    $response->assertNotFound();
});
