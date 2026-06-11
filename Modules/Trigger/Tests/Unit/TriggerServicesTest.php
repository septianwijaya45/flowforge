<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Modules\Tenant\Contracts\TenantContextContract;
use Modules\Tenant\Models\Tenant;
use Modules\Trigger\Contracts\CronTriggerServiceContract;
use Modules\Trigger\Contracts\ManualTriggerServiceContract;
use Modules\Trigger\Contracts\WebhookTriggerServiceContract;
use Modules\Trigger\Contracts\WorkflowTriggerServiceContract;
use Modules\Trigger\DTOs\CreateWorkflowTriggerDTO;
use Modules\Trigger\DTOs\DispatchTriggerDTO;
use Modules\Trigger\Enums\TriggerType;
use Modules\Trigger\Exceptions\TriggerException;
use Modules\Trigger\Models\WorkflowTrigger;
use Modules\Workflow\Enums\WorkflowStatus;
use Modules\Workflow\Models\Workflow;
use Modules\Workflow\Models\WorkflowVersion;
use Modules\WorkflowEngine\Enums\WorkflowTriggerType;

uses(RefreshDatabase::class);

/**
 * @return array{tenant: Tenant, workflow: Workflow}
 */
function triggerServiceContext(): array
{
    $tenant = Tenant::query()->create([
        'name' => 'Service Tenant',
        'slug' => 'service-tenant-'.uniqid(),
        'is_active' => true,
    ]);

    app(TenantContextContract::class)->set($tenant);

    $workflow = Workflow::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Service Workflow',
        'slug' => 'service-workflow',
        'status' => WorkflowStatus::Active,
    ]);

    $version = WorkflowVersion::query()->create([
        'tenant_id' => $tenant->id,
        'workflow_id' => $workflow->id,
        'version_number' => 1,
        'definition' => [
            'entry_node_id' => 'A',
            'nodes' => [
                ['id' => 'A', 'type' => 'http', 'config' => ['url' => 'https://example.com']],
            ],
            'edges' => [],
        ],
    ]);

    $workflow->update(['current_version_id' => $version->id]);

    return [
        'tenant' => $tenant,
        'workflow' => $workflow->refresh(),
    ];
}

it('dispatches manual workflow runs', function (): void {
    $context = triggerServiceContext();

    $run = app(ManualTriggerServiceContract::class)->fire(
        $context['workflow'],
        new DispatchTriggerDTO(input: ['key' => 'value'], triggeredBy: null),
    );

    expect($run->trigger_type)->toBe(WorkflowTriggerType::Manual)
        ->and($run->input)->toBe(['key' => 'value']);
});

it('dispatches webhook workflow runs by token', function (): void {
    $context = triggerServiceContext();

    $trigger = WorkflowTrigger::query()->create([
        'tenant_id' => $context['tenant']->id,
        'workflow_id' => $context['workflow']->id,
        'type' => TriggerType::Webhook,
        'name' => 'Hook',
        'is_active' => true,
        'webhook_token' => 'test-webhook-token',
    ]);

    $run = app(WebhookTriggerServiceContract::class)->handle(
        'test-webhook-token',
        new DispatchTriggerDTO(input: ['payload' => true]),
    );

    expect($run->trigger_type)->toBe(WorkflowTriggerType::Webhook)
        ->and($run->trigger_payload['trigger_id'])->toBe($trigger->id);
});

it('processes due cron triggers', function (): void {
    Carbon::setTestNow('2026-06-11 12:00:00');

    $context = triggerServiceContext();

    WorkflowTrigger::query()->create([
        'tenant_id' => $context['tenant']->id,
        'workflow_id' => $context['workflow']->id,
        'type' => TriggerType::Cron,
        'name' => 'Every Minute',
        'is_active' => true,
        'config' => ['expression' => '* * * * *'],
    ]);

    $result = app(CronTriggerServiceContract::class)->processDueTriggers();

    expect($result->processedCount)->toBe(1)
        ->and($result->runs[0]->trigger_type)->toBe(WorkflowTriggerType::Schedule);

    Carbon::setTestNow();
});

it('throws when workflow has no current version', function (): void {
    $tenant = Tenant::query()->create([
        'name' => 'No Version Tenant',
        'slug' => 'no-version-tenant-'.uniqid(),
        'is_active' => true,
    ]);

    app(TenantContextContract::class)->set($tenant);

    $workflow = Workflow::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Unpublished',
        'slug' => 'unpublished',
        'status' => WorkflowStatus::Draft,
    ]);

    app(ManualTriggerServiceContract::class)->fire(
        $workflow,
        new DispatchTriggerDTO,
    );
})->throws(TriggerException::class);

it('rejects invalid cron expressions on create', function (): void {
    $context = triggerServiceContext();

    app(WorkflowTriggerServiceContract::class)->create(
        $context['workflow'],
        new CreateWorkflowTriggerDTO(
            type: TriggerType::Cron,
            name: 'Broken',
            config: ['expression' => 'invalid'],
            isActive: true,
            createdBy: null,
        ),
    );
})->throws(TriggerException::class);
