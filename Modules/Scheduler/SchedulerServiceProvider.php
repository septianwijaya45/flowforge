<?php

declare(strict_types=1);

namespace Modules\Scheduler;

use App\Support\Modules\ModuleServiceProvider;

class SchedulerServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Scheduler';
    }
}
