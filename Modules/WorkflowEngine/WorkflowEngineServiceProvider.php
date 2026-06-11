<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine;

use App\Support\Modules\ModuleServiceProvider;
use Modules\WorkflowEngine\Console\ExplainWorkflowRunQueriesCommand;
use Modules\WorkflowEngine\Contracts\DelaySleeperContract;
use Modules\WorkflowEngine\Contracts\WorkflowExecutionEngineContract;
use Modules\WorkflowEngine\Contracts\WorkflowExecutionLogContract;
use Modules\WorkflowEngine\Contracts\WorkflowExecutionStatePersisterContract;
use Modules\WorkflowEngine\Contracts\WorkflowGraphValidatorContract;
use Modules\WorkflowEngine\Contracts\WorkflowParallelExecutorContract;
use Modules\WorkflowEngine\Contracts\WorkflowRunBroadcastContract;
use Modules\WorkflowEngine\Contracts\WorkflowStepExecutorFactoryContract;
use Modules\WorkflowEngine\Contracts\WorkflowTimeoutManagerContract;
use Modules\WorkflowEngine\Contracts\WorkflowTopologicalSorterContract;
use Modules\WorkflowEngine\Services\Executors\ConditionalNodeExecutor;
use Modules\WorkflowEngine\Services\Executors\DatabaseNodeExecutor;
use Modules\WorkflowEngine\Services\Executors\DelayNodeExecutor;
use Modules\WorkflowEngine\Services\Executors\EmailNodeExecutor;
use Modules\WorkflowEngine\Services\Executors\HttpNodeExecutor;
use Modules\WorkflowEngine\Services\Executors\ScriptNodeExecutor;
use Modules\WorkflowEngine\Services\Executors\WebhookNodeExecutor;
use Modules\WorkflowEngine\Services\Support\WorkflowContextInterpolator;
use Modules\WorkflowEngine\Services\Support\SecondsDelaySleeper;
use Modules\WorkflowEngine\Services\SyncWorkflowParallelExecutor;
use Modules\WorkflowEngine\Services\NullWorkflowExecutionLogger;
use Modules\WorkflowEngine\Services\NullWorkflowRunBroadcaster;
use Modules\WorkflowEngine\Services\WorkflowExecutionEngine;
use Modules\WorkflowEngine\Services\WorkflowExecutionStatePersister;
use Modules\WorkflowEngine\Services\WorkflowGraphValidator;
use Modules\WorkflowEngine\Services\WorkflowStepExecutorFactory;
use Modules\WorkflowEngine\Services\WorkflowTimeoutManager;
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
            WorkflowRunBroadcastContract::class,
            NullWorkflowRunBroadcaster::class,
        );

        $this->app->singleton(
            WorkflowExecutionLogContract::class,
            NullWorkflowExecutionLogger::class,
        );

        $this->app->singleton(
            WorkflowExecutionStatePersisterContract::class,
            WorkflowExecutionStatePersister::class,
        );

        $this->app->singleton(
            DelaySleeperContract::class,
            SecondsDelaySleeper::class,
        );

        $this->app->singleton(WorkflowContextInterpolator::class);

        $this->app->singleton(
            WorkflowStepExecutorFactoryContract::class,
            static fn ($app): WorkflowStepExecutorFactory => new WorkflowStepExecutorFactory(
                $app->make(HttpNodeExecutor::class),
                $app->make(DelayNodeExecutor::class),
                $app->make(ConditionalNodeExecutor::class),
                $app->make(ScriptNodeExecutor::class),
                $app->make(EmailNodeExecutor::class),
                $app->make(DatabaseNodeExecutor::class),
                $app->make(WebhookNodeExecutor::class),
            ),
        );

        $this->app->singleton(
            WorkflowExecutionEngineContract::class,
            WorkflowExecutionEngine::class,
        );

        $this->app->singleton(
            WorkflowTimeoutManagerContract::class,
            WorkflowTimeoutManager::class,
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ExplainWorkflowRunQueriesCommand::class,
            ]);
        }

        parent::boot();
    }
}
