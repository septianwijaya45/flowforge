<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Modules\ExecutionLog\Contracts\ExecutionLogRepositoryContract;
use Modules\ExecutionLog\Contracts\ExecutionLogWriterServiceContract;
use Modules\Tenant\Models\Tenant;
use Modules\WorkflowEngine\Contracts\WorkflowExecutionEngineContract;
use Modules\WorkflowEngine\DTOs\ExecuteWorkflowRunDTO;
use Modules\WorkflowEngine\Enums\WorkflowRunStatus;
use Modules\WorkflowEngine\Enums\WorkflowRunStepStatus;
use Tests\Support\ApiTestContext;

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
 * @return array<string, mixed>
 */
function endToEndWorkflowDefinition(): array
{
    return [
        'entry_node_id' => 'start',
        'nodes' => [
            ['id' => 'start', 'type' => 'delay', 'config' => ['seconds' => 0]],
            ['id' => 'finish', 'type' => 'script', 'config' => []],
        ],
        'edges' => [
            ['id' => 'e1', 'source' => 'start', 'target' => 'finish'],
        ],
    ];
}

describe('Workflow end-to-end', function (): void {
    it('creates a workflow, runs it, and verifies completion', function (): void {
        $tenant = Tenant::query()->create([
            'name' => 'E2E Tenant',
            'slug' => 'e2e-tenant-'.uniqid(),
            'is_active' => true,
        ]);

        $headers = ApiTestContext::headers($tenant);

        $createResponse = $this->postJson('/api/v1/workflows', [
            'name' => 'E2E Workflow',
            'description' => 'End-to-end execution test',
        ], $headers);

        $createResponse->assertCreated()
            ->assertJsonPath('data.workflow.name', 'E2E Workflow')
            ->assertJsonPath('data.workflow.status', 'draft');

        $workflowId = $createResponse->json('data.workflow.id');

        $versionResponse = $this->postJson(
            "/api/v1/workflows/{$workflowId}/versions",
            [
                'definition' => endToEndWorkflowDefinition(),
                'change_summary' => 'Initial publish for E2E test',
            ],
            $headers,
        );

        $versionResponse->assertCreated()
            ->assertJsonPath('data.version.version_number', 1)
            ->assertJsonPath('data.version.is_current', true);

        $triggerResponse = $this->postJson(
            "/api/v1/workflows/{$workflowId}/trigger/manual",
            ['input' => ['source' => 'e2e-test']],
            $headers,
        );

        $triggerResponse->assertCreated()
            ->assertJsonPath('data.run.status', 'pending')
            ->assertJsonPath('data.run.trigger_type', 'manual');

        $runId = $triggerResponse->json('data.run.id');

        $executionResult = app(WorkflowExecutionEngineContract::class)->execute(
            new ExecuteWorkflowRunDTO($runId),
        );

        expect($executionResult->status)->toBe(WorkflowRunStatus::Success);

        app(ExecutionLogWriterServiceContract::class)->flush();

        $monitorResponse = $this->getJson(
            "/api/v1/monitoring/runs/{$runId}",
            $headers,
        );

        $monitorResponse->assertSuccessful()
            ->assertJsonPath('data.run.id', $runId)
            ->assertJsonPath('data.run.status', 'success')
            ->assertJsonPath('data.run.workflow_id', $workflowId)
            ->assertJsonPath('data.run.input.source', 'e2e-test')
            ->assertJsonStructure([
                'data' => [
                    'run' => [
                        'started_at',
                        'completed_at',
                        'steps' => [
                            '*' => ['node_id', 'status', 'execution_order'],
                        ],
                    ],
                ],
            ]);

        $steps = $monitorResponse->json('data.run.steps');

        expect($steps)->toHaveCount(2)
            ->and(collect($steps)->pluck('node_id')->all())->toBe(['start', 'finish'])
            ->and(collect($steps)->pluck('status')->unique()->all())->toBe(['success']);

        $this->assertDatabaseHas('workflow_runs', [
            'id' => $runId,
            'workflow_id' => $workflowId,
            'status' => WorkflowRunStatus::Success->value,
        ]);

        $this->assertDatabaseHas('workflow_run_steps', [
            'workflow_run_id' => $runId,
            'node_id' => 'start',
            'status' => WorkflowRunStepStatus::Success->value,
        ]);

        $this->assertDatabaseHas('workflow_run_steps', [
            'workflow_run_id' => $runId,
            'node_id' => 'finish',
            'status' => WorkflowRunStepStatus::Success->value,
        ]);

        $logs = app(ExecutionLogRepositoryContract::class)->forRun($runId);

        expect($logs)->not->toBeEmpty()
            ->and($logs->pluck('message')->contains('Workflow run started'))->toBeTrue()
            ->and($logs->pluck('message')->contains('Workflow run completed successfully'))->toBeTrue();

        $logResponse = $this->getJson("/api/v1/execution-logs/runs/{$runId}", $headers);

        $logResponse->assertSuccessful()
            ->assertJsonPath('data.workflow_run_id', $runId);

        expect($logResponse->json('data.logs'))->not->toBeEmpty();
    });
});
