<?php

declare(strict_types=1);

namespace Modules\Tenant\Contracts;

use Modules\Tenant\Models\Tenant;

interface TenantContextContract
{
    public function set(Tenant $tenant): void;

    public function tenant(): ?Tenant;

    public function tenantId(): ?string;

    public function hasTenant(): bool;

    public function clear(): void;
}
