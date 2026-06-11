<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

uses(RefreshDatabase::class);
use Modules\Tenant\Contracts\TenantContextResolverContract;
use Modules\Tenant\Exceptions\TenantResolutionException;
use Modules\Tenant\Models\Tenant;
use Modules\Tenant\Services\TenantContextResolver;

it('resolves tenant by id header', function (): void {
    $tenant = Tenant::query()->create([
        'name' => 'Acme Corp',
        'slug' => 'acme-corp-'.uniqid(),
        'is_active' => true,
    ]);

    $request = Request::create('/api/v1/workflows', 'GET');
    $request->headers->set(TenantContextResolver::TENANT_ID_HEADER, $tenant->id);

    $resolved = app(TenantContextResolverContract::class)->resolveFromRequest($request);

    expect($resolved->id)->toBe($tenant->id);
});

it('resolves tenant by slug header', function (): void {
    $slug = 'slug-tenant-'.uniqid();

    $tenant = Tenant::query()->create([
        'name' => 'Slug Tenant',
        'slug' => $slug,
        'is_active' => true,
    ]);

    $request = Request::create('/api/v1/workflows', 'GET');
    $request->headers->set(TenantContextResolver::TENANT_SLUG_HEADER, $slug);

    $resolved = app(TenantContextResolverContract::class)->resolveFromRequest($request);

    expect($resolved->id)->toBe($tenant->id);
});

it('throws when tenant identifier is missing', function (): void {
    $request = Request::create('/api/v1/workflows', 'GET');

    app(TenantContextResolverContract::class)->resolveFromRequest($request);
})->throws(TenantResolutionException::class);

it('throws when tenant is not found', function (): void {
    $request = Request::create('/api/v1/workflows', 'GET');
    $request->headers->set(
        TenantContextResolver::TENANT_ID_HEADER,
        '99999999-9999-9999-9999-999999999999',
    );

    app(TenantContextResolverContract::class)->resolveFromRequest($request);
})->throws(TenantResolutionException::class);

it('throws when tenant is inactive', function (): void {
    $tenant = Tenant::query()->create([
        'name' => 'Inactive Tenant',
        'slug' => 'inactive-tenant-'.uniqid(),
        'is_active' => false,
    ]);

    $request = Request::create('/api/v1/workflows', 'GET');
    $request->headers->set(TenantContextResolver::TENANT_ID_HEADER, $tenant->id);

    app(TenantContextResolverContract::class)->resolveFromRequest($request);
})->throws(TenantResolutionException::class);
