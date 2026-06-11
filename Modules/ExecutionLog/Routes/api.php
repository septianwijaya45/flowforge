<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\ExecutionLog\Http\Controllers\ExecutionLogController;
use Modules\Tenant\Http\Middleware\EnsureTenantContext;

Route::prefix('execution-logs')
    ->middleware(['auth:web,api', EnsureTenantContext::class])
    ->group(function (): void {
        Route::get('runs/{run}', [ExecutionLogController::class, 'forRun']);
    });
