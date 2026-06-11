<?php

declare(strict_types=1);

namespace Modules\Tenant\Services;

use Illuminate\Http\Request;
use Modules\Tenant\Contracts\TenantContextContract;
use Modules\Tenant\Contracts\TenantContextResolverContract;
use Modules\Tenant\Exceptions\TenantResolutionException;

class TenantRouteContextResolver
{
    public function __construct(
        private readonly TenantContextResolverContract $headerResolver,
        private readonly SessionTenantResolver $sessionResolver,
        private readonly TenantContextContract $context,
    ) {}

    /**
     * @throws TenantResolutionException
     */
    public function ensureResolved(Request $request): void
    {
        if ($this->context->hasTenant()) {
            return;
        }

        try {
            $this->context->set($this->headerResolver->resolveFromRequest($request));

            return;
        } catch (TenantResolutionException) {
        }

        $tenant = $this->sessionResolver->resolve($request);

        if ($tenant !== null) {
            $this->context->set($tenant);

            return;
        }

        throw TenantResolutionException::notProvided();
    }
}
