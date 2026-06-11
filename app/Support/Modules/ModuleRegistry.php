<?php

declare(strict_types=1);

namespace App\Support\Modules;

use Modules\AI\AIServiceProvider;
use Modules\Auth\AuthServiceProvider;
use Modules\Monitoring\MonitoringServiceProvider;
use Modules\Retry\RetryServiceProvider;
use Modules\Scheduler\SchedulerServiceProvider;
use Modules\Tenant\TenantServiceProvider;
use Modules\Workflow\WorkflowServiceProvider;
use Modules\WorkflowEngine\WorkflowEngineServiceProvider;
use Modules\WorkflowVersioning\WorkflowVersioningServiceProvider;

/**
 * Central registry of application modules.
 *
 * Provider order reflects migration and boot dependencies:
 * Auth → Tenant → Workflow → WorkflowEngine → WorkflowVersioning → supporting modules.
 */
class ModuleRegistry
{
    /**
     * @return list<class-string>
     */
    public static function providers(): array
    {
        return [
            AuthServiceProvider::class,
            TenantServiceProvider::class,
            WorkflowServiceProvider::class,
            WorkflowEngineServiceProvider::class,
            WorkflowVersioningServiceProvider::class,
            RetryServiceProvider::class,
            MonitoringServiceProvider::class,
            SchedulerServiceProvider::class,
            AIServiceProvider::class,
        ];
    }
}
