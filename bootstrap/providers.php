<?php

use App\Providers\AppServiceProvider;
use App\Support\Modules\ModuleRegistry;

return [
    AppServiceProvider::class,
    ...ModuleRegistry::providers(),
];
