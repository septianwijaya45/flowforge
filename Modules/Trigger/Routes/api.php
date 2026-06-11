<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Tenant\Http\Middleware\EnsureTenantContext;
use Modules\Trigger\Http\Controllers\CronTriggerController;
use Modules\Trigger\Http\Controllers\ManualTriggerController;
use Modules\Trigger\Http\Controllers\WebhookTriggerController;
use Modules\Trigger\Http\Controllers\WorkflowTriggerController;

Route::post('webhooks/{token}', [WebhookTriggerController::class, 'handle']);

Route::middleware(['auth:web,api', EnsureTenantContext::class])->group(function (): void {
    Route::middleware('role:admin,editor')->group(function (): void {
        Route::post('triggers/cron/process', [CronTriggerController::class, 'process']);
    });

    Route::prefix('workflows/{workflow}')->group(function (): void {
        Route::middleware('role:admin,editor,viewer')->group(function (): void {
            Route::get('triggers', [WorkflowTriggerController::class, 'index']);
        });

        Route::middleware('role:admin,editor')->group(function (): void {
            Route::post('triggers', [WorkflowTriggerController::class, 'store']);
            Route::put('triggers/{trigger}', [WorkflowTriggerController::class, 'update']);
            Route::delete('triggers/{trigger}', [WorkflowTriggerController::class, 'destroy']);
            Route::post('trigger/manual', [ManualTriggerController::class, 'fire']);
        });
    });
});
