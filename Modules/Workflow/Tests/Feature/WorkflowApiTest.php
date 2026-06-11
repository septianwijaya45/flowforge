<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Auth\Enums\UserRole;
use Modules\Auth\Models\User;
use Modules\Tenant\Models\Tenant;
use Modules\Workflow\Enums\WorkflowStatus;
use Modules\Workflow\Models\Workflow;
use Tests\Support\ApiTestContext;

uses(RefreshDatabase::class);

/**
 * @return array<string, string>
 */
function workflowApiHeaders(Tenant $tenant, ?User $user = null, UserRole $role = UserRole::Editor): array
{
    return ApiTestContext::headers($tenant, $user, $role);
}

it('lists workflows with pagination', function (): void {
    $tenant = Tenant::query()->create([
        'name' => 'List Tenant',
        'slug' => 'list-tenant-'.uniqid(),
        'is_active' => true,
    ]);

    foreach (range(1, 20) as $index) {
        Workflow::query()->create([
            'tenant_id' => $tenant->id,
            'name' => "Workflow {$index}",
            'slug' => "workflow-{$index}-".uniqid(),
            'status' => WorkflowStatus::Draft,
        ]);
    }

    $response = $this->getJson('/api/v1/workflows?per_page=10&page=2', workflowApiHeaders($tenant));

    $response->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.pagination.current_page', 2)
        ->assertJsonPath('data.pagination.per_page', 10)
        ->assertJsonPath('data.pagination.total', 20)
        ->assertJsonCount(10, 'data.workflows');
});

it('filters workflows by status and search term', function (): void {
    $tenant = Tenant::query()->create([
        'name' => 'Filter Tenant',
        'slug' => 'filter-tenant-'.uniqid(),
        'is_active' => true,
    ]);

    Workflow::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Billing Pipeline',
        'slug' => 'billing-pipeline',
        'status' => WorkflowStatus::Active,
    ]);

    Workflow::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Onboarding Flow',
        'slug' => 'onboarding-flow',
        'status' => WorkflowStatus::Draft,
    ]);

    $response = $this->getJson(
        '/api/v1/workflows?status=active&search=billing',
        workflowApiHeaders($tenant),
    );

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data.workflows')
        ->assertJsonPath('data.workflows.0.name', 'Billing Pipeline');
});

it('shows a single workflow', function (): void {
    $tenant = Tenant::query()->create([
        'name' => 'Show Tenant',
        'slug' => 'show-tenant-'.uniqid(),
        'is_active' => true,
    ]);

    $workflow = Workflow::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Visible Workflow',
        'slug' => 'visible-workflow',
        'description' => 'Shown via API',
        'status' => WorkflowStatus::Active,
    ]);

    $response = $this->getJson(
        "/api/v1/workflows/{$workflow->id}",
        workflowApiHeaders($tenant),
    );

    $response->assertSuccessful()
        ->assertJsonPath('data.workflow.id', $workflow->id)
        ->assertJsonPath('data.workflow.name', 'Visible Workflow')
        ->assertJsonPath('data.workflow.description', 'Shown via API');
});

it('creates a workflow', function (): void {
    $tenant = Tenant::query()->create([
        'name' => 'Create Tenant',
        'slug' => 'create-tenant-'.uniqid(),
        'is_active' => true,
    ]);

    $response = $this->postJson('/api/v1/workflows', [
        'name' => 'New Workflow',
        'description' => 'A test workflow',
    ], workflowApiHeaders($tenant));

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.workflow.name', 'New Workflow')
        ->assertJsonPath('data.workflow.slug', 'new-workflow')
        ->assertJsonPath('data.workflow.status', 'draft');

    $this->assertDatabaseHas('workflows', [
        'tenant_id' => $tenant->id,
        'name' => 'New Workflow',
        'slug' => 'new-workflow',
    ]);
});

it('validates workflow creation input', function (): void {
    $tenant = Tenant::query()->create([
        'name' => 'Validate Tenant',
        'slug' => 'validate-tenant-'.uniqid(),
        'is_active' => true,
    ]);

    $response = $this->postJson('/api/v1/workflows', [
        'name' => '',
        'slug' => 'Invalid Slug',
    ], workflowApiHeaders($tenant));

    $response->assertUnprocessable();
});

it('updates a workflow', function (): void {
    $tenant = Tenant::query()->create([
        'name' => 'Update Tenant',
        'slug' => 'update-tenant-'.uniqid(),
        'is_active' => true,
    ]);

    $workflow = Workflow::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Original Name',
        'slug' => 'original-name',
        'status' => WorkflowStatus::Draft,
    ]);

    $response = $this->putJson("/api/v1/workflows/{$workflow->id}", [
        'name' => 'Updated Name',
        'status' => 'active',
    ], workflowApiHeaders($tenant));

    $response->assertSuccessful()
        ->assertJsonPath('data.workflow.name', 'Updated Name')
        ->assertJsonPath('data.workflow.status', 'active');

    $this->assertDatabaseHas('workflows', [
        'id' => $workflow->id,
        'name' => 'Updated Name',
        'status' => 'active',
    ]);
});

it('soft deletes a workflow', function (): void {
    $tenant = Tenant::query()->create([
        'name' => 'Delete Tenant',
        'slug' => 'delete-tenant-'.uniqid(),
        'is_active' => true,
    ]);

    $workflow = Workflow::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Delete Me',
        'slug' => 'delete-me',
        'status' => WorkflowStatus::Draft,
    ]);

    $response = $this->deleteJson(
        "/api/v1/workflows/{$workflow->id}",
        [],
        workflowApiHeaders($tenant),
    );

    $response->assertSuccessful()
        ->assertJsonPath('message', 'Workflow deleted');

    $this->assertSoftDeleted('workflows', ['id' => $workflow->id]);
});

it('isolates workflows by tenant', function (): void {
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
        'name' => 'Tenant B Workflow',
        'slug' => 'tenant-b-workflow',
        'status' => WorkflowStatus::Draft,
    ]);

    $response = $this->putJson(
        "/api/v1/workflows/{$workflowB->id}",
        ['name' => 'Hijacked'],
        workflowApiHeaders($tenantA),
    );

    $response->assertNotFound();

    $this->assertDatabaseHas('workflows', [
        'id' => $workflowB->id,
        'name' => 'Tenant B Workflow',
    ]);
});

it('denies viewers from mutating workflows', function (): void {
    $tenant = Tenant::query()->create([
        'name' => 'Viewer Tenant',
        'slug' => 'viewer-tenant-'.uniqid(),
        'is_active' => true,
    ]);

    $response = $this->postJson('/api/v1/workflows', [
        'name' => 'Blocked Workflow',
    ], workflowApiHeaders($tenant, role: UserRole::Viewer));

    $response->assertForbidden()
        ->assertJsonPath('success', false);
});

it('requires authentication', function (): void {
    $tenant = Tenant::query()->create([
        'name' => 'Auth Tenant',
        'slug' => 'auth-tenant-'.uniqid(),
        'is_active' => true,
    ]);

    $response = $this->getJson('/api/v1/workflows', [
        'X-Tenant-Id' => $tenant->id,
    ]);

    $response->assertUnauthorized();
});

it('requires a tenant header', function (): void {
    User::factory()->create([
        'email' => 'no-tenant@example.com',
        'password' => 'password',
    ]);

    $loginResponse = $this->postJson('/api/v1/auth/login', [
        'email' => 'no-tenant@example.com',
        'password' => 'password',
    ]);

    $response = $this->getJson('/api/v1/workflows', [
        'Authorization' => 'Bearer '.$loginResponse->json('data.access_token'),
    ]);

    $response->assertBadRequest();
});

it('validates list query parameters', function (): void {
    $tenant = Tenant::query()->create([
        'name' => 'List Validate Tenant',
        'slug' => 'list-validate-tenant-'.uniqid(),
        'is_active' => true,
    ]);

    $response = $this->getJson(
        '/api/v1/workflows?per_page=500&sort=invalid',
        workflowApiHeaders($tenant),
    );

    $response->assertUnprocessable();
});
