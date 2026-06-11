<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use Modules\WorkflowEngine\Jobs\ExecuteWorkflowRunJob;
use Modules\Auth\Models\User;
use Modules\Tenant\Models\Tenant;
use Modules\Trigger\Enums\TriggerType;
use Modules\Trigger\Models\WorkflowTrigger;
use Modules\Workflow\Enums\WorkflowStatus;
use Modules\Workflow\Models\Workflow;
use Modules\Workflow\Models\WorkflowVersion;
use Modules\WorkflowEngine\Enums\WorkflowRunStatus;
use Modules\WorkflowEngine\Enums\WorkflowTriggerType;
use Tests\Support\ApiTestContext;

uses(RefreshDatabase::class);

/**
 * @return array<string, mixed>
 */
function triggerWorkflowDefinition(): array
{
    return [
        'entry_node_id' => 'A',
        'nodes' => [
            ['id' => 'A', 'type' => 'http', 'config' => ['url' => 'https://example.com']],
        ],
        'edges' => [],
    ];
}

/**
 * @return array{tenant: Tenant, workflow: Workflow, headers: array<string, string>}
 */
function triggerApiContext(): array
{
    $tenant = Tenant::query()->create([
        'name' => 'Trigger Tenant',
        'slug' => 'trigger-tenant-'.uniqid(),
        'is_active' => true,
    ]);

    $workflow = Workflow::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Triggered Workflow',
        'slug' => 'triggered-workflow-'.uniqid(),
        'status' => WorkflowStatus::Active,
    ]);

    $version = WorkflowVersion::query()->create([
        'tenant_id' => $tenant->id,
        'workflow_id' => $workflow->id,
        'version_number' => 1,
        'definition' => triggerWorkflowDefinition(),
    ]);

    $workflow->update(['current_version_id' => $version->id]);

    return [
        'tenant' => $tenant,
        'workflow' => $workflow->refresh(),
        'headers' => ApiTestContext::headers($tenant),
    ];
}

it('creates manual cron and webhook triggers', function (): void {
    $context = triggerApiContext();

    $manual = $this->postJson(
        "/api/v1/workflows/{$context['workflow']->id}/triggers",
        ['type' => 'manual', 'name' => 'Manual Run'],
        $context['headers'],
    );

    $cron = $this->postJson(
        "/api/v1/workflows/{$context['workflow']->id}/triggers",
        [
            'type' => 'cron',
            'name' => 'Hourly',
            'config' => ['expression' => '0 * * * *'],
        ],
        $context['headers'],
    );

    $webhook = $this->postJson(
        "/api/v1/workflows/{$context['workflow']->id}/triggers",
        ['type' => 'webhook', 'name' => 'Inbound Hook'],
        $context['headers'],
    );

    $manual->assertCreated()->assertJsonPath('data.trigger.type', 'manual');
    $cron->assertCreated()->assertJsonPath('data.trigger.config.expression', '0 * * * *');
    $webhook->assertCreated()
        ->assertJsonPath('data.trigger.type', 'webhook')
        ->assertJsonStructure(['data' => ['trigger' => ['webhook_token']]]);
});

it('rejects invalid cron expressions', function (): void {
    $context = triggerApiContext();

    $response = $this->postJson(
        "/api/v1/workflows/{$context['workflow']->id}/triggers",
        [
            'type' => 'cron',
            'name' => 'Broken',
            'config' => ['expression' => 'not-a-cron'],
        ],
        $context['headers'],
    );

    $response->assertUnprocessable();
});

it('rejects manual triggers when the workflow has no published version', function (): void {
    $context = triggerApiContext();
    $context['workflow']->update(['current_version_id' => null]);

    $response = $this->postJson(
        "/api/v1/workflows/{$context['workflow']->id}/trigger/manual",
        ['input' => ['source' => 'dashboard']],
        $context['headers'],
    );

    $response->assertUnprocessable()
        ->assertJsonPath('success', false);

    $this->assertDatabaseCount('workflow_runs', 0);
});

it('fires a manual trigger and creates a pending workflow run', function (): void {
    Queue::fake();
    $context = triggerApiContext();

    $response = $this->postJson(
        "/api/v1/workflows/{$context['workflow']->id}/trigger/manual",
        ['input' => ['source' => 'dashboard']],
        $context['headers'],
    );

    $response->assertCreated()
        ->assertJsonPath('data.run.status', 'pending')
        ->assertJsonPath('data.run.trigger_type', 'manual')
        ->assertJsonPath('data.run.input.source', 'dashboard');

    Queue::assertPushed(ExecuteWorkflowRunJob::class, function (ExecuteWorkflowRunJob $job) use ($response): bool {
        return $job->runId === $response->json('data.run.id');
    });

    $this->assertDatabaseHas('workflow_runs', [
        'workflow_id' => $context['workflow']->id,
        'status' => WorkflowRunStatus::Pending->value,
        'trigger_type' => WorkflowTriggerType::Manual->value,
    ]);
});

it('handles webhook triggers without authentication', function (): void {
    Queue::fake();
    $context = triggerApiContext();

    $createResponse = $this->postJson(
        "/api/v1/workflows/{$context['workflow']->id}/triggers",
        ['type' => 'webhook', 'name' => 'Public Hook'],
        $context['headers'],
    );

    $token = $createResponse->json('data.trigger.webhook_token');

    $response = $this->postJson("/api/v1/webhooks/{$token}", [
        'input' => ['event' => 'invoice.paid'],
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.run.trigger_type', 'webhook')
        ->assertJsonPath('data.run.input.event', 'invoice.paid');
});

it('processes due cron triggers', function (): void {
    Queue::fake();
    Carbon::setTestNow('2026-06-11 10:00:00');

    $context = triggerApiContext();

    WorkflowTrigger::query()->create([
        'tenant_id' => $context['tenant']->id,
        'workflow_id' => $context['workflow']->id,
        'type' => TriggerType::Cron,
        'name' => 'Every Minute',
        'is_active' => true,
        'config' => ['expression' => '* * * * *'],
    ]);

    $response = $this->postJson('/api/v1/triggers/cron/process', [], $context['headers']);

    $response->assertSuccessful()
        ->assertJsonPath('data.processed_count', 1)
        ->assertJsonPath('data.runs.0.trigger_type', 'schedule');

    Carbon::setTestNow();
});

it('updates and deletes workflow triggers', function (): void {
    $context = triggerApiContext();

    $createResponse = $this->postJson(
        "/api/v1/workflows/{$context['workflow']->id}/triggers",
        ['type' => 'manual', 'name' => 'Original Name'],
        $context['headers'],
    );

    $triggerId = $createResponse->json('data.trigger.id');

    $updateResponse = $this->putJson(
        "/api/v1/workflows/{$context['workflow']->id}/triggers/{$triggerId}",
        ['name' => 'Renamed Trigger', 'is_active' => false],
        $context['headers'],
    );

    $updateResponse->assertSuccessful()
        ->assertJsonPath('data.trigger.name', 'Renamed Trigger')
        ->assertJsonPath('data.trigger.is_active', false);

    $deleteResponse = $this->deleteJson(
        "/api/v1/workflows/{$context['workflow']->id}/triggers/{$triggerId}",
        [],
        $context['headers'],
    );

    $deleteResponse->assertSuccessful();

    $this->assertSoftDeleted('workflow_triggers', ['id' => $triggerId]);
});

it('lists triggers for a workflow', function (): void {
    $context = triggerApiContext();

    WorkflowTrigger::query()->create([
        'tenant_id' => $context['tenant']->id,
        'workflow_id' => $context['workflow']->id,
        'type' => TriggerType::Manual,
        'name' => 'Manual',
        'is_active' => true,
    ]);

    $response = $this->getJson(
        "/api/v1/workflows/{$context['workflow']->id}/triggers",
        $context['headers'],
    );

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data.triggers')
        ->assertJsonPath('data.triggers.0.name', 'Manual');
});

it('isolates triggers by tenant', function (): void {
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
        'name' => 'Other Workflow',
        'slug' => 'other-workflow',
        'status' => WorkflowStatus::Active,
    ]);

    $user = User::factory()->create([
        'email' => 'tenant-a-trigger@example.com',
        'password' => 'password',
    ]);

    $loginResponse = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response = $this->getJson(
        "/api/v1/workflows/{$workflowB->id}/triggers",
        [
            'Authorization' => 'Bearer '.$loginResponse->json('data.access_token'),
            'X-Tenant-Id' => $tenantA->id,
        ],
    );

    $response->assertNotFound();
});
