<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine;

use App\Support\Modules\ModuleServiceProvider;

class WorkflowEngineServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'WorkflowEngine';
    }
}
