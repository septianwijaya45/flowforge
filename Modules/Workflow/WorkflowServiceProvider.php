<?php

declare(strict_types=1);

namespace Modules\Workflow;

use App\Support\Modules\ModuleServiceProvider;

class WorkflowServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Workflow';
    }
}
