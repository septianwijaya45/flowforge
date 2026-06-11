<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine;

use App\Support\Modules\ModuleServiceProvider;
use Modules\WorkflowEngine\Contracts\WorkflowGraphValidatorContract;
use Modules\WorkflowEngine\Contracts\WorkflowTopologicalSorterContract;
use Modules\WorkflowEngine\Services\WorkflowGraphValidator;
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
    }
}
