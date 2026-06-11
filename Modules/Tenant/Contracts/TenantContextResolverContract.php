<?php

declare(strict_types=1);

namespace Modules\Tenant\Contracts;

use Illuminate\Http\Request;
use Modules\Tenant\Models\Tenant;

interface TenantContextResolverContract
{
    public function resolveFromRequest(Request $request): Tenant;
}
