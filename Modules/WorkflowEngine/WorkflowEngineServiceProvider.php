<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine;

use App\Support\Modules\ModuleServiceProvider;
use Modules\WorkflowEngine\Contracts\WorkflowExecutionEngineContract;
use Modules\WorkflowEngine\Contracts\WorkflowExecutionStatePersisterContract;
use Modules\WorkflowEngine\Contracts\WorkflowGraphValidatorContract;
use Modules\WorkflowEngine\Contracts\WorkflowParallelExecutorContract;
use Modules\WorkflowEngine\Contracts\WorkflowTopologicalSorterContract;
use Modules\WorkflowEngine\Services\Executors\ConditionNodeExecutor;
use Modules\WorkflowEngine\Services\Executors\DelayNodeExecutor;
use Modules\WorkflowEngine\Services\Executors\HttpNodeExecutor;
use Modules\WorkflowEngine\Services\Executors\ScriptNodeExecutor;
use Modules\WorkflowEngine\Services\SyncWorkflowParallelExecutor;
use Modules\WorkflowEngine\Services\WorkflowExecutionEngine;
use Modules\WorkflowEngine\Services\WorkflowExecutionStatePersister;
use Modules\WorkflowEngine\Services\WorkflowGraphValidator;
use Modules\WorkflowEngine\Services\WorkflowNodeExecutorRegistry;
use Modules\WorkflowEngine\Services\WorkflowTopologicalSorter;

class WorkflowEngineServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'WorkflowEngine';
    }

    public function register(): void
    {
        $this->app->singleton(
            WorkflowGraphValidatorContract::class,
            WorkflowGraphValidator::class,
        );

        $this->app->singleton(
            WorkflowTopologicalSorterContract::class,
            WorkflowTopologicalSorter::class,
        );

        $this->app->singleton(
            WorkflowParallelExecutorContract::class,
            SyncWorkflowParallelExecutor::class,
        );

        $this->app->singleton(
            WorkflowExecutionStatePersisterContract::class,
            WorkflowExecutionStatePersister::class,
        );

        $this->app->singleton(WorkflowNodeExecutorRegistry::class, function ($app): WorkflowNodeExecutorRegistry {
            return new WorkflowNodeExecutorRegistry([
                $app->make(HttpNodeExecutor::class),
                $app->make(DelayNodeExecutor::class),
                $app->make(ConditionNodeExecutor::class),
                $app->make(ScriptNodeExecutor::class),
            ]);
        });

        $this->app->singleton(
            WorkflowExecutionEngineContract::class,
            WorkflowExecutionEngine::class,
        );
    }
}
