<?php

declare(strict_types=1);

namespace Modules\Tenant;

use App\Support\Modules\ModuleServiceProvider;
use Illuminate\Foundation\Http\Kernel;
use Illuminate\Routing\Router;
use Modules\Tenant\Contracts\TenantContextContract;
use Modules\Tenant\Contracts\TenantContextResolverContract;
use Modules\Tenant\Http\Middleware\EnsureTenantContext;
use Modules\Tenant\Services\TenantContext;
use Modules\Tenant\Services\TenantContextResolver;

class TenantServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Tenant';
    }

    public function register(): void
    {
        $this->app->scoped(TenantContextContract::class, TenantContext::class);

        $this->app->singleton(
            TenantContextResolverContract::class,
            TenantContextResolver::class,
        );
    }

    public function boot(): void
    {
        /** @var Router $router */
        $router = $this->app->make(Router::class);

        $router->aliasMiddleware('tenant', EnsureTenantContext::class);
        $router->pushMiddlewareToGroup('api', EnsureTenantContext::class);

        /** @var Kernel $kernel */
        $kernel = $this->app->make(Kernel::class);
        $kernel->prependToMiddlewarePriority(EnsureTenantContext::class);

        parent::boot();
    }
}
