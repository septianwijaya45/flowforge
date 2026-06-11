<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Tenant\Http\Middleware\EnsureTenantContext;
use Modules\WorkflowVersioning\Http\Controllers\WorkflowVersionController;

Route::prefix('workflows/{workflow}')
    ->middleware(['auth:web,api', EnsureTenantContext::class])
    ->group(function (): void {
        Route::middleware('role:admin,editor,viewer')->group(function (): void {
            Route::get('versions/current', [WorkflowVersionController::class, 'current']);
            Route::get('versions', [WorkflowVersionController::class, 'index']);
        });

        Route::middleware('role:admin,editor')->group(function (): void {
            Route::post('versions', [WorkflowVersionController::class, 'store']);
            Route::post('versions/{version}/rollback', [WorkflowVersionController::class, 'rollback']);
        });
    });
