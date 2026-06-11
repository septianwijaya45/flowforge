<?php

declare(strict_types=1);

namespace Modules\Tenant\Support;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Modules\Tenant\Exceptions\TenantResolutionException;
use Modules\Tenant\Services\TenantRouteContextResolver;

trait ResolvesRouteTenantContext
{
    /**
     * @template TModel of object
     *
     * @param  class-string<TModel>  $modelClass
     */
    protected function ensureRouteTenantContext(string $modelClass, string|int $value): void
    {
        try {
            app(TenantRouteContextResolver::class)->ensureResolved(request());
        } catch (TenantResolutionException) {
            throw (new ModelNotFoundException)->setModel($modelClass, [$value]);
        }
    }
}
