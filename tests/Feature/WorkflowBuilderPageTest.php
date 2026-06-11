<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Auth\Models\User;
use Modules\Tenant\Models\Tenant;
use Modules\Workflow\Enums\WorkflowStatus;
use Modules\Workflow\Models\Workflow;

uses(RefreshDatabase::class);

it('loads the workflow builder page for an authenticated web user', function (): void {
    $tenant = Tenant::query()->create([
        'name' => 'Builder Tenant',
        'slug' => 'builder-tenant-'.uniqid(),
        'is_active' => true,
    ]);

    $user = User::factory()->create([
        'email' => 'builder-'.uniqid().'@example.com',
    ]);

    $workflow = Workflow::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Builder Workflow',
        'slug' => 'builder-workflow-'.uniqid(),
        'status' => WorkflowStatus::Active,
        'created_by' => $user->id,
    ]);

    $response = $this->actingAs($user)
        ->withSession(['tenant_id' => $tenant->id])
        ->get("/workflows/{$workflow->id}/builder");

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('workflows/builder')
            ->where('workflowId', $workflow->id)
            ->where('workflowName', 'Builder Workflow'));
});
