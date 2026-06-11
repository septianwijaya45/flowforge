<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Auth\Models\User;
use Modules\Tenant\Models\Tenant;
use Modules\Workflow\Enums\WorkflowStatus;
use Modules\Workflow\Models\Workflow;
use Modules\Workflow\Models\WorkflowVersion;
use Modules\WorkflowEngine\Enums\WorkflowNodeType;
use Modules\WorkflowEngine\Enums\WorkflowRunStatus;
use Modules\WorkflowEngine\Enums\WorkflowRunStepStatus;
use Modules\WorkflowEngine\Enums\WorkflowTriggerType;
use Modules\WorkflowEngine\Models\WorkflowRun;
use Modules\WorkflowEngine\Models\WorkflowRunStep;

uses(RefreshDatabase::class);

/**
 * @return array{tenant: Tenant, headers: array<string, string>}
 */
function monitoringApiContext(): array
{
    $tenant = Tenant::query()->create([
        'name' => 'Monitoring Tenant',
        'slug' => 'monitoring-tenant-'.uniqid(),
        'is_active' => true,
    ]);

    $user = User::factory()->create([
        'email' => 'monitoring-'.uniqid().'@example.com',
        'password' => 'password',
    ]);

    $loginResponse = test()->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    return [
        'tenant' => $tenant,
        'headers' => [
            'Authorization' => 'Bearer '.$loginResponse->json('data.access_token'),
            'X-Tenant-Id' => $tenant->id,
        ],
    ];
}

it('lists active workflow runs for monitoring', function (): void {
    $context = monitoringApiContext();

    $workflow = Workflow::query()->create([
        'tenant_id' => $context['tenant']->id,
        'name' => 'Monitored Workflow',
        'slug' => 'monitored-'.uniqid(),
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

    $run = WorkflowRun::query()->create([
        'tenant_id' => $context['tenant']->id,
        'workflow_id' => $workflow->id,
        'workflow_version_id' => $version->id,
        'status' => WorkflowRunStatus::Running,
        'trigger_type' => WorkflowTriggerType::Manual,
        'started_at' => now(),
    ]);

    WorkflowRunStep::query()->create([
        'tenant_id' => $context['tenant']->id,
        'workflow_run_id' => $run->id,
        'node_id' => 'node-1',
        'node_type' => WorkflowNodeType::Http,
        'node_label' => 'Fetch',
        'status' => WorkflowRunStepStatus::Running,
        'execution_order' => 0,
    ]);

    $response = $this->getJson('/api/v1/monitoring/runs?active_only=1', $context['headers']);

    $response->assertSuccessful()
        ->assertJsonPath('data.runs.0.id', $run->id)
        ->assertJsonPath('data.runs.0.status', 'running')
        ->assertJsonPath('data.runs.0.workflow_name', 'Monitored Workflow');
});

it('returns a workflow run with live step statuses', function (): void {
    $context = monitoringApiContext();

    $workflow = Workflow::query()->create([
        'tenant_id' => $context['tenant']->id,
        'name' => 'Detail Workflow',
        'slug' => 'detail-'.uniqid(),
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

    $run = WorkflowRun::query()->create([
        'tenant_id' => $context['tenant']->id,
        'workflow_id' => $workflow->id,
        'workflow_version_id' => $version->id,
        'status' => WorkflowRunStatus::Running,
        'trigger_type' => WorkflowTriggerType::Manual,
    ]);

    WorkflowRunStep::query()->create([
        'tenant_id' => $context['tenant']->id,
        'workflow_run_id' => $run->id,
        'node_id' => 'node-1',
        'node_type' => WorkflowNodeType::Http,
        'node_label' => 'Step A',
        'status' => WorkflowRunStepStatus::Success,
        'execution_order' => 0,
    ]);

    WorkflowRunStep::query()->create([
        'tenant_id' => $context['tenant']->id,
        'workflow_run_id' => $run->id,
        'node_id' => 'node-2',
        'node_type' => WorkflowNodeType::Delay,
        'node_label' => 'Step B',
        'status' => WorkflowRunStepStatus::Running,
        'execution_order' => 1,
    ]);

    $response = $this->getJson("/api/v1/monitoring/runs/{$run->id}", $context['headers']);

    $response->assertSuccessful()
        ->assertJsonPath('data.run.id', $run->id)
        ->assertJsonCount(2, 'data.run.steps')
        ->assertJsonPath('data.run.steps.1.status', 'running');
});
