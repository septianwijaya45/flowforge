<?php

declare(strict_types=1);

namespace Modules\WorkflowVersioning;

use App\Support\Modules\ModuleServiceProvider;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Route;
use Modules\Tenant\Contracts\TenantContextContract;
use Modules\Tenant\Support\ResolvesRouteTenantContext;
use Modules\Workflow\Models\Workflow;
use Modules\Workflow\Models\WorkflowVersion;
use Modules\WorkflowVersioning\Contracts\WorkflowVersioningServiceContract;
use Modules\WorkflowVersioning\Services\WorkflowVersioningService;

class WorkflowVersioningServiceProvider extends ModuleServiceProvider
{
    use ResolvesRouteTenantContext;

    public function moduleName(): string
    {
        return 'WorkflowVersioning';
    }

    public function register(): void
    {
        $this->app->singleton(
            WorkflowVersioningServiceContract::class,
            WorkflowVersioningService::class,
        );
    }

    public function boot(): void
    {
        Route::bind('version', function (string $value, \Illuminate\Routing\Route $route): WorkflowVersion {
            $workflow = $route->parameter('workflow');

            if (! $workflow instanceof Workflow) {
                throw (new ModelNotFoundException)->setModel(WorkflowVersion::class, [$value]);
            }

            $this->ensureRouteTenantContext(WorkflowVersion::class, $value);

            $context = app(TenantContextContract::class);

            $version = WorkflowVersion::query()
                ->whereKey($value)
                ->where('workflow_id', $workflow->id)
                ->where('tenant_id', $context->tenantId())
                ->first();

            if ($version === null) {
                throw (new ModelNotFoundException)->setModel(WorkflowVersion::class, [$value]);
            }

            return $version;
        });

        parent::boot();
    }
}
