<?php

declare(strict_types=1);

namespace Modules\Workflow;

use App\Support\Modules\ModuleServiceProvider;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Route;
use Modules\Tenant\Contracts\TenantContextContract;
use Modules\Tenant\Contracts\TenantContextResolverContract;
use Modules\Tenant\Exceptions\TenantResolutionException;
use Modules\Workflow\Contracts\WorkflowServiceContract;
use Modules\Workflow\Models\Workflow;
use Modules\Workflow\Services\WorkflowService;

class WorkflowServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Workflow';
    }

    public function register(): void
    {
        $this->app->singleton(WorkflowServiceContract::class, WorkflowService::class);
    }

    public function boot(): void
    {
        Route::bind('workflow', function (string $value): Workflow {
            $context = app(TenantContextContract::class);

            if (! $context->hasTenant()) {
                try {
                    $context->set(
                        app(TenantContextResolverContract::class)->resolveFromRequest(request()),
                    );
                } catch (TenantResolutionException) {
                    throw (new ModelNotFoundException)->setModel(Workflow::class, [$value]);
                }
            }

            $workflow = Workflow::query()
                ->whereKey($value)
                ->where('tenant_id', $context->tenantId())
                ->first();

            if ($workflow === null) {
                throw (new ModelNotFoundException)->setModel(Workflow::class, [$value]);
            }

            return $workflow;
        });

        parent::boot();
    }
}
