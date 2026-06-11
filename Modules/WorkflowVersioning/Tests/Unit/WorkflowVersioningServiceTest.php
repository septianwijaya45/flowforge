<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Tenant\Contracts\TenantContextContract;
use Modules\Tenant\Models\Tenant;
use Modules\Workflow\Enums\WorkflowStatus;
use Modules\Workflow\Models\Workflow;
use Modules\Workflow\Models\WorkflowVersion;
use Modules\WorkflowVersioning\Contracts\WorkflowVersioningServiceContract;
use Modules\WorkflowVersioning\DTOs\CreateWorkflowVersionDTO;
use Modules\WorkflowVersioning\DTOs\ListWorkflowVersionsDTO;
use Modules\WorkflowVersioning\DTOs\RollbackWorkflowVersionDTO;
use Modules\WorkflowVersioning\Exceptions\WorkflowVersioningException;

uses(RefreshDatabase::class);

/**
 * @return array<string, mixed>
 */
function serviceWorkflowDefinition(): array
{
    return [
        'entry_node_id' => 'A',
        'nodes' => [
            ['id' => 'A', 'type' => 'http', 'config' => ['url' => 'https://example.com']],
        ],
        'edges' => [],
    ];
}

it('creates immutable workflow versions in sequence', function (): void {
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
        'status' => WorkflowStatus::Draft,
    ]);

    $service = app(WorkflowVersioningServiceContract::class);

    $first = $service->createVersion($workflow, new CreateWorkflowVersionDTO(
        definition: serviceWorkflowDefinition(),
        changeSummary: 'First',
        createdBy: null,
    ));

    $second = $service->createVersion($workflow, new CreateWorkflowVersionDTO(
        definition: array_merge(serviceWorkflowDefinition(), ['entry_node_id' => 'A']),
        changeSummary: 'Second',
        createdBy: null,
    ));

    expect($first->version_number)->toBe(1)
        ->and($second->version_number)->toBe(2)
        ->and($workflow->refresh()->current_version_id)->toBe($second->id);
});

it('returns workflow version history', function (): void {
    $tenant = Tenant::query()->create([
        'name' => 'History Tenant',
        'slug' => 'history-tenant-'.uniqid(),
        'is_active' => true,
    ]);

    app(TenantContextContract::class)->set($tenant);

    $workflow = Workflow::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'History Workflow',
        'slug' => 'history-workflow',
        'status' => WorkflowStatus::Draft,
    ]);

    WorkflowVersion::query()->create([
        'tenant_id' => $tenant->id,
        'workflow_id' => $workflow->id,
        'version_number' => 1,
        'definition' => serviceWorkflowDefinition(),
    ]);

    $paginator = app(WorkflowVersioningServiceContract::class)->history(
        $workflow,
        new ListWorkflowVersionsDTO(page: 1, perPage: 10),
    );

    expect($paginator->total())->toBe(1)
        ->and($paginator->items()[0]->version_number)->toBe(1);
});

it('rolls back by persisting a new version', function (): void {
    $tenant = Tenant::query()->create([
        'name' => 'Rollback Tenant',
        'slug' => 'rollback-tenant-'.uniqid(),
        'is_active' => true,
    ]);

    app(TenantContextContract::class)->set($tenant);

    $workflow = Workflow::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Rollback Workflow',
        'slug' => 'rollback-workflow',
        'status' => WorkflowStatus::Draft,
    ]);

    $service = app(WorkflowVersioningServiceContract::class);

    $original = $service->createVersion($workflow, new CreateWorkflowVersionDTO(
        definition: serviceWorkflowDefinition(),
        changeSummary: 'Original',
        createdBy: null,
    ));

    $service->createVersion($workflow, new CreateWorkflowVersionDTO(
        definition: [
            'entry_node_id' => 'B',
            'nodes' => [
                ['id' => 'B', 'type' => 'delay', 'config' => ['seconds' => 2]],
            ],
            'edges' => [],
        ],
        changeSummary: 'Changed',
        createdBy: null,
    ));

    $rolledBack = $service->rollback(
        $workflow,
        $original,
        new RollbackWorkflowVersionDTO(changeSummary: null, createdBy: null),
    );

    expect($rolledBack->version_number)->toBe(3)
        ->and($rolledBack->definition)->toBe($original->definition)
        ->and($rolledBack->change_summary)->toBe('Rolled back to version 1');
});

it('throws when rollback target does not belong to workflow', function (): void {
    $tenant = Tenant::query()->create([
        'name' => 'Mismatch Tenant',
        'slug' => 'mismatch-tenant-'.uniqid(),
        'is_active' => true,
    ]);

    app(TenantContextContract::class)->set($tenant);

    $workflowA = Workflow::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Workflow A',
        'slug' => 'workflow-a',
        'status' => WorkflowStatus::Draft,
    ]);

    $workflowB = Workflow::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Workflow B',
        'slug' => 'workflow-b',
        'status' => WorkflowStatus::Draft,
    ]);

    $foreignVersion = WorkflowVersion::query()->create([
        'tenant_id' => $tenant->id,
        'workflow_id' => $workflowB->id,
        'version_number' => 1,
        'definition' => serviceWorkflowDefinition(),
    ]);

    app(WorkflowVersioningServiceContract::class)->rollback(
        $workflowA,
        $foreignVersion,
        new RollbackWorkflowVersionDTO(changeSummary: null, createdBy: null),
    );
})->throws(WorkflowVersioningException::class);
