<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Modules\Auth\Models\User;
use Modules\ExecutionLog\Contracts\ExecutionLogWriterServiceContract;
use Modules\ExecutionLog\DTOs\AppendExecutionLogDTO;
use Modules\ExecutionLog\Enums\ExecutionLogLevel;
use Modules\Tenant\Models\Tenant;
use Modules\Workflow\Enums\WorkflowStatus;
use Modules\Workflow\Models\Workflow;
use Modules\Workflow\Models\WorkflowVersion;
use Modules\WorkflowEngine\Contracts\WorkflowExecutionEngineContract;
use Modules\WorkflowEngine\DTOs\ExecuteWorkflowRunDTO;
use Modules\WorkflowEngine\Enums\WorkflowRunStatus;
use Modules\WorkflowEngine\Enums\WorkflowTriggerType;
use Modules\WorkflowEngine\Models\WorkflowRun;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $connection = (string) config('execution_log.connection', 'execution_logs');
    $schema = Schema::connection($connection);

    $schema->dropIfExists('execution_logs');
    $schema->dropIfExists('migrations');

    Artisan::call('migrate', [
        '--database' => $connection,
        '--path' => base_path('Modules/ExecutionLog/Database/Migrations'),
        '--realpath' => true,
        '--force' => true,
    ]);
});

/**
 * @return array{tenant: Tenant, headers: array<string, string>}
 */
function executionLogApiContext(): array
{
    $tenant = Tenant::query()->create([
        'name' => 'Execution Log Tenant',
        'slug' => 'execution-log-tenant-'.uniqid(),
        'is_active' => true,
    ]);

    $user = User::factory()->create([
        'email' => 'execution-log-'.uniqid().'@example.com',
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

it('returns execution logs for a workflow run', function (): void {
    $context = executionLogApiContext();

    $workflow = Workflow::query()->create([
        'tenant_id' => $context['tenant']->id,
        'name' => 'Logged Workflow',
        'slug' => 'logged-'.uniqid(),
        'status' => WorkflowStatus::Active,
    ]);

    $version = WorkflowVersion::query()->create([
        'tenant_id' => $context['tenant']->id,
        'workflow_id' => $workflow->id,
        'version_number' => 1,
        'definition' => [
            'entry_node_id' => 'start',
            'nodes' => [
                ['id' => 'start', 'type' => 'delay', 'config' => ['seconds' => 0]],
                ['id' => 'finish', 'type' => 'script', 'config' => []],
            ],
            'edges' => [
                ['id' => 'e1', 'source' => 'start', 'target' => 'finish'],
            ],
        ],
    ]);

    $run = WorkflowRun::query()->create([
        'tenant_id' => $context['tenant']->id,
        'workflow_id' => $workflow->id,
        'workflow_version_id' => $version->id,
        'status' => WorkflowRunStatus::Pending,
        'trigger_type' => WorkflowTriggerType::Manual,
    ]);

    app(WorkflowExecutionEngineContract::class)->execute(
        new ExecuteWorkflowRunDTO($run->id),
    );

    app(ExecutionLogWriterServiceContract::class)->flush();

    $response = $this->getJson("/api/v1/execution-logs/runs/{$run->id}", $context['headers']);

    $response->assertSuccessful()
        ->assertJsonPath('data.workflow_run_id', $run->id)
        ->assertJsonStructure([
            'data' => [
                'workflow_run_id',
                'logs' => [
                    '*' => [
                        'id',
                        'workflow_run_id',
                        'level',
                        'message',
                        'logged_at',
                    ],
                ],
            ],
        ]);

    expect($response->json('data.logs'))->not->toBeEmpty();
});

it('stores structured execution logs when writing manually', function (): void {
    $context = executionLogApiContext();

    $workflow = Workflow::query()->create([
        'tenant_id' => $context['tenant']->id,
        'name' => 'Manual Log Workflow',
        'slug' => 'manual-log-'.uniqid(),
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
        'status' => WorkflowRunStatus::Success,
        'trigger_type' => WorkflowTriggerType::Manual,
    ]);

    app(ExecutionLogWriterServiceContract::class)->log(
        new AppendExecutionLogDTO(
            tenantId: $context['tenant']->id,
            level: ExecutionLogLevel::Info,
            message: 'Manual test log',
            workflowId: $workflow->id,
            workflowRunId: $run->id,
        ),
    );

    app(ExecutionLogWriterServiceContract::class)->flush();

    $response = $this->getJson("/api/v1/execution-logs/runs/{$run->id}?limit=10", $context['headers']);

    $response->assertSuccessful()
        ->assertJsonPath('data.logs.0.message', 'Manual test log')
        ->assertJsonPath('data.logs.0.level', 'info');
});
