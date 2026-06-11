<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Modules\Tenant\Http\Middleware\EnsureTenantContext;
use Modules\Tenant\Models\Tenant;
use Modules\Tenant\Services\TenantContextResolver;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Route::middleware(['api', EnsureTenantContext::class])
        ->get('/api/v1/test-tenant-scope', fn () => response()->json(['ok' => true]));

    Route::middleware(['api', EnsureTenantContext::class])
        ->get('/api/v1/tenants/probe', fn () => response()->json(['ok' => true]));
});

it('rejects api requests without tenant headers', function (): void {
    $response = $this->getJson('/api/v1/test-tenant-scope');

    $response->assertBadRequest()
        ->assertJsonPath('message', 'A tenant identifier must be provided via the X-Tenant-Id or X-Tenant-Slug header.');
});

it('allows tenant management routes without tenant headers', function (): void {
    $response = $this->getJson('/api/v1/tenants/probe');

    $response->assertSuccessful();
});

it('accepts api requests with a valid tenant header', function (): void {
    $tenant = Tenant::query()->create([
        'name' => 'Middleware Tenant',
        'slug' => 'middleware-tenant-'.uniqid(),
        'is_active' => true,
    ]);

    $response = $this->getJson('/api/v1/test-tenant-scope', [
        TenantContextResolver::TENANT_ID_HEADER => $tenant->id,
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('ok', true);
});
