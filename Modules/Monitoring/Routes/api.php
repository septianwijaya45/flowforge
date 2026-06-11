<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Monitoring\Http\Controllers\WorkflowRunMonitorController;
use Modules\Tenant\Http\Middleware\EnsureTenantContext;

Route::prefix('monitoring')
    ->middleware(['auth:web,api', EnsureTenantContext::class])
    ->group(function (): void {
        Route::get('metrics', [WorkflowRunMonitorController::class, 'metrics']);
        Route::get('runs', [WorkflowRunMonitorController::class, 'index']);
        Route::get('runs/{run}', [WorkflowRunMonitorController::class, 'show']);
    });
