<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Tenant\Contracts\TenantContextContract;

uses(RefreshDatabase::class);
use Modules\Tenant\Models\Tenant;
use Modules\Workflow\Enums\WorkflowStatus;
use Modules\Workflow\Models\Workflow;

it('scopes workflow queries to the active tenant context', function (): void {
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

    Workflow::query()->create([
        'tenant_id' => $tenantA->id,
        'name' => 'Workflow A',
        'slug' => 'workflow-a-'.uniqid(),
        'status' => WorkflowStatus::Draft,
    ]);

    Workflow::query()->create([
        'tenant_id' => $tenantB->id,
        'name' => 'Workflow B',
        'slug' => 'workflow-b-'.uniqid(),
        'status' => WorkflowStatus::Draft,
    ]);

    $context = app(TenantContextContract::class);
    $context->set($tenantA);

    expect(Workflow::query()->count())->toBe(1)
        ->and(Workflow::query()->first()?->name)->toBe('Workflow A');

    $context->clear();

    expect(Workflow::query()->count())->toBe(2);
});

it('auto assigns tenant_id on create when context is set', function (): void {
    $tenant = Tenant::query()->create([
        'name' => 'Auto Tenant',
        'slug' => 'auto-tenant-'.uniqid(),
        'is_active' => true,
    ]);

    app(TenantContextContract::class)->set($tenant);

    $workflow = Workflow::query()->create([
        'name' => 'Auto Workflow',
        'slug' => 'auto-workflow-'.uniqid(),
        'status' => WorkflowStatus::Draft,
    ]);

    expect($workflow->tenant_id)->toBe($tenant->id);
});
