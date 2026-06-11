<?php

declare(strict_types=1);

namespace Modules\Tenant\Services;

use Modules\Tenant\Contracts\TenantContextContract;
use Modules\Tenant\Models\Tenant;

class TenantContext implements TenantContextContract
{
    private ?Tenant $tenant = null;

    public function set(Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function tenant(): ?Tenant
    {
        return $this->tenant;
    }

    public function tenantId(): ?string
    {
        return $this->tenant?->id;
    }

    public function hasTenant(): bool
    {
        return $this->tenant !== null;
    }

    public function clear(): void
    {
        $this->tenant = null;
    }
}
