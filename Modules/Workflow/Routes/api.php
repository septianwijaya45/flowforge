<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Tenant\Http\Middleware\EnsureTenantContext;
use Modules\Workflow\Http\Controllers\WorkflowController;

Route::prefix('workflows')
    ->middleware(['auth:web,api', EnsureTenantContext::class])
    ->group(function (): void {
        Route::middleware('role:admin,editor,viewer')->group(function (): void {
            Route::get('/', [WorkflowController::class, 'index']);
            Route::get('/{workflow}', [WorkflowController::class, 'show']);
        });

        Route::middleware('role:admin,editor')->group(function (): void {
            Route::post('/', [WorkflowController::class, 'store']);
            Route::put('/{workflow}', [WorkflowController::class, 'update']);
            Route::delete('/{workflow}', [WorkflowController::class, 'destroy']);
        });
    });
