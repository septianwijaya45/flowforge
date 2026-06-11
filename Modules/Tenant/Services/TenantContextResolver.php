<?php

declare(strict_types=1);

namespace Modules\Tenant\Services;

use Illuminate\Http\Request;
use Modules\Tenant\Contracts\TenantContextResolverContract;
use Modules\Tenant\Exceptions\TenantResolutionException;
use Modules\Tenant\Models\Tenant;

class TenantContextResolver implements TenantContextResolverContract
{
    public const TENANT_ID_HEADER = 'X-Tenant-Id';

    public const TENANT_SLUG_HEADER = 'X-Tenant-Slug';

    public function resolveFromRequest(Request $request): Tenant
    {
        $tenantId = $request->header(self::TENANT_ID_HEADER);
        $tenantSlug = $request->header(self::TENANT_SLUG_HEADER);

        if ($tenantId === null && $tenantSlug === null) {
            throw TenantResolutionException::notProvided();
        }

        $tenant = match (true) {
            $tenantId !== null => Tenant::query()->find($tenantId),
            default => Tenant::query()->where('slug', $tenantSlug)->first(),
        };

        $identifier = $tenantId ?? (string) $tenantSlug;

        if ($tenant === null) {
            throw TenantResolutionException::notFound($identifier);
        }

        if (! $tenant->is_active) {
            throw TenantResolutionException::inactive($tenant->id);
        }

        return $tenant;
    }
}
