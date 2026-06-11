<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Workflow\Enums\WorkflowStatus;
use Modules\Workflow\Models\Workflow;
use Modules\Workflow\Models\WorkflowVersion;
use Modules\WorkflowEngine\Enums\WorkflowRunStatus;
use Modules\WorkflowEngine\Enums\WorkflowTriggerType;
use Modules\WorkflowEngine\Models\WorkflowRun;

uses(RefreshDatabase::class);

it('returns monitoring dashboard metrics', function (): void {
    $context = monitoringApiContext();

    $workflow = Workflow::query()->create([
        'tenant_id' => $context['tenant']->id,
        'name' => 'Metrics Workflow',
        'slug' => 'metrics-'.uniqid(),
        'status' => WorkflowStatus::Active,
    ]);

    $version = WorkflowVersion::query()->create([
        'tenant_id' => $context['tenant']->id,
        'workflow_id' => $workflow->id,
        'version_number' => 1,
        'definition' => [
            'entry_node_id' => 'node-1',
            'nodes' => [['id' => 'node-1', 'type' => 'http', 'config' => []]],
            'edges' => [],
        ],
    ]);

    WorkflowRun::query()->create([
        'tenant_id' => $context['tenant']->id,
        'workflow_id' => $workflow->id,
        'workflow_version_id' => $version->id,
        'status' => WorkflowRunStatus::Running,
        'trigger_type' => WorkflowTriggerType::Manual,
        'started_at' => now(),
    ]);

    WorkflowRun::query()->create([
        'tenant_id' => $context['tenant']->id,
        'workflow_id' => $workflow->id,
        'workflow_version_id' => $version->id,
        'status' => WorkflowRunStatus::Success,
        'trigger_type' => WorkflowTriggerType::Manual,
        'started_at' => now()->subSeconds(10),
        'completed_at' => now(),
    ]);

    WorkflowRun::query()->create([
        'tenant_id' => $context['tenant']->id,
        'workflow_id' => $workflow->id,
        'workflow_version_id' => $version->id,
        'status' => WorkflowRunStatus::Failed,
        'trigger_type' => WorkflowTriggerType::Manual,
        'started_at' => now()->subSeconds(5),
        'completed_at' => now(),
    ]);

    $response = $this->getJson('/api/v1/monitoring/metrics?days=7', $context['headers']);

    $response->assertSuccessful()
        ->assertJsonPath('data.metrics.active_runs', 1)
        ->assertJsonPath('data.metrics.success_rate', 50)
        ->assertJsonPath('data.metrics.failure_rate', 50)
        ->assertJsonPath('data.metrics.totals.completed', 2)
        ->assertJsonStructure([
            'data' => [
                'metrics' => [
                    'active_runs',
                    'success_rate',
                    'failure_rate',
                    'avg_execution_time_ms',
                    'totals',
                    'daily',
                ],
            ],
        ]);
});
