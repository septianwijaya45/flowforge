<?php

declare(strict_types=1);

namespace Modules\Scheduler;

use App\Support\Modules\ModuleServiceProvider;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Route;
use Modules\Scheduler\Contracts\ScheduleServiceContract;
use Modules\Scheduler\Services\ScheduleService;
use Modules\Tenant\Contracts\TenantContextContract;
use Modules\Tenant\Support\ResolvesRouteTenantContext;
use Modules\Trigger\Enums\TriggerType;
use Modules\Trigger\Models\WorkflowTrigger;

class SchedulerServiceProvider extends ModuleServiceProvider
{
    use ResolvesRouteTenantContext;

    public function moduleName(): string
    {
        return 'Scheduler';
    }

    public function register(): void
    {
        $this->app->singleton(ScheduleServiceContract::class, ScheduleService::class);
    }

    public function boot(): void
    {
        Route::bind('schedule', function (string $value): WorkflowTrigger {
            $this->ensureRouteTenantContext(WorkflowTrigger::class, $value);

            $context = app(TenantContextContract::class);

            $schedule = WorkflowTrigger::query()
                ->whereKey($value)
                ->where('type', TriggerType::Cron)
                ->where('tenant_id', $context->tenantId())
                ->first();

            if ($schedule === null) {
                throw (new ModelNotFoundException)->setModel(WorkflowTrigger::class, [$value]);
            }

            return $schedule;
        });

        parent::boot();
    }
}
