<?php

declare(strict_types=1);

namespace Modules\AI;

use App\Support\Modules\ModuleServiceProvider;

class AIServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'AI';
    }
}
