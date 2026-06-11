<?php

declare(strict_types=1);

namespace Modules\Tenant\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Modules\Tenant\Contracts\TenantContextContract;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $context = app(TenantContextContract::class);

        if (! $context->hasTenant()) {
            return;
        }

        $builder->where(
            $model->getTable().'.tenant_id',
            $context->tenantId(),
        );
    }
}
