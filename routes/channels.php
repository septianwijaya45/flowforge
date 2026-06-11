<?php

use Illuminate\Support\Facades\Broadcast;
use Modules\Tenant\Services\TenantContextResolver;
use Modules\WorkflowEngine\Models\WorkflowRun;

Broadcast::channel('workflow-runs.{runId}', function ($user, string $runId): bool {
    if ($user === null) {
        return false;
    }

    $tenantId = request()->header(TenantContextResolver::TENANT_ID_HEADER);

    if ($tenantId === null) {
        return false;
    }

    return WorkflowRun::query()
        ->whereKey($runId)
        ->where('tenant_id', $tenantId)
        ->exists();
});

Broadcast::channel('tenants.{tenantId}.workflow-runs', function ($user, string $tenantId): bool {
    if ($user === null) {
        return false;
    }

    $requestTenantId = request()->header(TenantContextResolver::TENANT_ID_HEADER);

    return $requestTenantId !== null && $requestTenantId === $tenantId;
});
