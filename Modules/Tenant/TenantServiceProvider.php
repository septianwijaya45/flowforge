<?php

declare(strict_types=1);

namespace Modules\Tenant;

use App\Support\Modules\ModuleServiceProvider;

class TenantServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Tenant';
    }
}
