<?php

declare(strict_types=1);

namespace Modules\Tenant\Services;

use Illuminate\Http\Request;
use Modules\Tenant\Models\Tenant;

class SessionTenantResolver
{
    public function resolve(Request $request): ?Tenant
    {
        if ($request->user() === null) {
            return null;
        }

        $tenantId = $request->session()->get('tenant_id');
        $tenant = $tenantId !== null
            ? Tenant::query()->find($tenantId)
            : Tenant::query()->where('is_active', true)->orderBy('created_at')->first();

        if ($tenant === null || ! $tenant->is_active) {
            return null;
        }

        if ($request->session()->get('tenant_id') !== $tenant->id) {
            $request->session()->put('tenant_id', $tenant->id);
        }

        return $tenant;
    }
}
