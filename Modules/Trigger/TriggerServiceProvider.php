<?php

declare(strict_types=1);

namespace Modules\Trigger;

use App\Support\Modules\ModuleServiceProvider;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Route;
use Modules\Tenant\Contracts\TenantContextContract;
use Modules\Tenant\Contracts\TenantContextResolverContract;
use Modules\Tenant\Exceptions\TenantResolutionException;
use Modules\Trigger\Contracts\CronTriggerServiceContract;
use Modules\Trigger\Contracts\ManualTriggerServiceContract;
use Modules\Trigger\Contracts\TriggerDispatcherContract;
use Modules\Trigger\Contracts\WebhookTriggerServiceContract;
use Modules\Trigger\Contracts\WorkflowTriggerServiceContract;
use Modules\Trigger\Models\WorkflowTrigger;
use Modules\Trigger\Services\CronTriggerService;
use Modules\Trigger\Services\ManualTriggerService;
use Modules\Trigger\Services\TriggerDispatcher;
use Modules\Trigger\Services\WebhookTriggerService;
use Modules\Trigger\Services\WorkflowTriggerService;
use Modules\Workflow\Models\Workflow;

class TriggerServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Trigger';
    }

    public function register(): void
    {
        $this->app->singleton(TriggerDispatcherContract::class, TriggerDispatcher::class);
        $this->app->singleton(WorkflowTriggerServiceContract::class, WorkflowTriggerService::class);
        $this->app->singleton(ManualTriggerServiceContract::class, ManualTriggerService::class);
        $this->app->singleton(WebhookTriggerServiceContract::class, WebhookTriggerService::class);
        $this->app->singleton(CronTriggerServiceContract::class, CronTriggerService::class);
    }

    public function boot(): void
    {
        Route::bind('trigger', function (string $value, \Illuminate\Routing\Route $route): WorkflowTrigger {
            $workflow = $route->parameter('workflow');

            if (! $workflow instanceof Workflow) {
                throw (new ModelNotFoundException)->setModel(WorkflowTrigger::class, [$value]);
            }

            $context = app(TenantContextContract::class);

            if (! $context->hasTenant()) {
                try {
                    $context->set(
                        app(TenantContextResolverContract::class)->resolveFromRequest(request()),
                    );
                } catch (TenantResolutionException) {
                    throw (new ModelNotFoundException)->setModel(WorkflowTrigger::class, [$value]);
                }
            }

            $trigger = WorkflowTrigger::query()
                ->whereKey($value)
                ->where('workflow_id', $workflow->id)
                ->where('tenant_id', $context->tenantId())
                ->first();

            if ($trigger === null) {
                throw (new ModelNotFoundException)->setModel(WorkflowTrigger::class, [$value]);
            }

            return $trigger;
        });

        parent::boot();
    }
}
