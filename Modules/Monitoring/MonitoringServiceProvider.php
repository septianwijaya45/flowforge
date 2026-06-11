<?php

declare(strict_types=1);

namespace Modules\Monitoring;

use App\Support\Modules\ModuleServiceProvider;

class MonitoringServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Monitoring';
    }
}
