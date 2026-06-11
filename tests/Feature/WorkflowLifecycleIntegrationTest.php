<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Modules\Tenant\Models\Tenant;
use Modules\Workflow\Models\WorkflowVersion;
use Modules\WorkflowEngine\Enums\WorkflowRunStatus;
use Modules\WorkflowEngine\Enums\WorkflowTriggerType;
use Tests\Support\ApiTestContext;

uses(RefreshDatabase::class);

/**
 * @return array<string, mixed>
 */
function lifecycleWorkflowDefinition(string $entryNodeId, string $url): array
{
    $terminalNodeId = $entryNodeId.'-finish';

    return [
        'entry_node_id' => $entryNodeId,
        'nodes' => [
            ['id' => $entryNodeId, 'type' => 'http', 'config' => ['url' => $url]],
            ['id' => $terminalNodeId, 'type' => 'script', 'config' => []],
        ],
        'edges' => [
            ['id' => 'e-'.$entryNodeId, 'source' => $entryNodeId, 'target' => $terminalNodeId],
        ],
    ];
}

describe('Workflow lifecycle integration', function (): void {
    it('creates a workflow, versions it, triggers it, rolls back, and re-triggers', function (): void {
        $tenant = Tenant::query()->create([
            'name' => 'Lifecycle Tenant',
            'slug' => 'lifecycle-tenant-'.uniqid(),
            'is_active' => true,
        ]);

        $headers = ApiTestContext::headers($tenant);

        $createResponse = $this->postJson('/api/v1/workflows', [
            'name' => 'Lifecycle Workflow',
            'description' => 'End-to-end integration test',
        ], $headers);

        $createResponse->assertCreated()
            ->assertJsonPath('data.workflow.status', 'draft');

        $workflowId = $createResponse->json('data.workflow.id');

        $versionOneResponse = $this->postJson(
            "/api/v1/workflows/{$workflowId}/versions",
            [
                'definition' => lifecycleWorkflowDefinition('v1', 'https://example.com/v1'),
                'change_summary' => 'Initial publish',
            ],
            $headers,
        );

        $versionOneResponse->assertCreated()
            ->assertJsonPath('data.version.version_number', 1);

        $versionOneId = $versionOneResponse->json('data.version.id');

        Queue::fake();

        $firstRunResponse = $this->postJson(
            "/api/v1/workflows/{$workflowId}/trigger/manual",
            ['input' => ['phase' => 'before-rollback']],
            $headers,
        );

        $firstRunResponse->assertCreated()
            ->assertJsonPath('data.run.status', 'pending')
            ->assertJsonPath('data.run.workflow_version_id', $versionOneId);

        $versionTwoResponse = $this->postJson(
            "/api/v1/workflows/{$workflowId}/versions",
            [
                'definition' => lifecycleWorkflowDefinition('v2', 'https://example.com/v2'),
                'change_summary' => 'Second publish',
            ],
            $headers,
        );

        $versionTwoResponse->assertCreated()
            ->assertJsonPath('data.version.version_number', 2)
            ->assertJsonPath('data.version.definition.entry_node_id', 'v2');

        $rollbackResponse = $this->postJson(
            "/api/v1/workflows/{$workflowId}/versions/{$versionOneId}/rollback",
            ['change_summary' => 'Restore v1 graph'],
            $headers,
        );

        $rollbackResponse->assertSuccessful()
            ->assertJsonPath('data.version.version_number', 3)
            ->assertJsonPath('data.version.definition.entry_node_id', 'v1')
            ->assertJsonPath('data.version.is_current', true);

        $rolledBackVersionId = $rollbackResponse->json('data.version.id');

        $currentResponse = $this->getJson(
            "/api/v1/workflows/{$workflowId}/versions/current",
            $headers,
        );

        $currentResponse->assertSuccessful()
            ->assertJsonPath('data.version.id', $rolledBackVersionId)
            ->assertJsonPath('data.version.definition.nodes.0.config.url', 'https://example.com/v1')
            ->assertJsonPath('data.version.definition.entry_node_id', 'v1');

        $secondRunResponse = $this->postJson(
            "/api/v1/workflows/{$workflowId}/trigger/manual",
            ['input' => ['phase' => 'after-rollback']],
            $headers,
        );

        $secondRunResponse->assertCreated()
            ->assertJsonPath('data.run.workflow_version_id', $rolledBackVersionId);

        expect(WorkflowVersion::query()->where('workflow_id', $workflowId)->count())->toBe(3);

        $this->assertDatabaseHas('workflow_runs', [
            'workflow_id' => $workflowId,
            'workflow_version_id' => $versionOneId,
            'status' => WorkflowRunStatus::Pending->value,
            'trigger_type' => WorkflowTriggerType::Manual->value,
        ]);

        $this->assertDatabaseHas('workflow_runs', [
            'workflow_id' => $workflowId,
            'workflow_version_id' => $rolledBackVersionId,
            'status' => WorkflowRunStatus::Pending->value,
            'trigger_type' => WorkflowTriggerType::Manual->value,
        ]);
    });
});
