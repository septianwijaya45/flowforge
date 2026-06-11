<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine;

use App\Support\Modules\ModuleServiceProvider;
use Modules\WorkflowEngine\Contracts\WorkflowGraphValidatorContract;
use Modules\WorkflowEngine\Services\WorkflowGraphValidator;

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
    }
}
