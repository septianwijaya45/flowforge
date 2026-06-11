<?php

declare(strict_types=1);

namespace Modules\Monitoring;

use App\Support\Modules\ModuleServiceProvider;
use Illuminate\Support\Facades\Broadcast;
use Modules\Monitoring\Contracts\WorkflowRunMonitorServiceContract;
use Modules\Monitoring\Services\ReverbWorkflowRunBroadcaster;
use Modules\Monitoring\Services\WorkflowRunMonitorService;
use Modules\WorkflowEngine\Contracts\WorkflowRunBroadcastContract;

class MonitoringServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Monitoring';
    }

    public function register(): void
    {
        $this->app->singleton(WorkflowRunMonitorServiceContract::class, WorkflowRunMonitorService::class);

        $this->app->singleton(WorkflowRunBroadcastContract::class, ReverbWorkflowRunBroadcaster::class);
    }

    public function boot(): void
    {
        parent::boot();

        Broadcast::routes(['middleware' => ['auth:api']]);
    }
}
