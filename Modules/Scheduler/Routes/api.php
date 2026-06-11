<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Scheduler\Http\Controllers\ScheduleController;
use Modules\Tenant\Http\Middleware\EnsureTenantContext;

Route::prefix('schedules')
    ->middleware(['auth:web,api', EnsureTenantContext::class])
    ->group(function (): void {
        Route::middleware('role:admin,editor,viewer')->group(function (): void {
            Route::get('/', [ScheduleController::class, 'index']);
            Route::get('/{schedule}', [ScheduleController::class, 'show']);
        });

        Route::middleware('role:admin,editor')->group(function (): void {
            Route::post('/', [ScheduleController::class, 'store']);
            Route::put('/{schedule}', [ScheduleController::class, 'update']);
            Route::delete('/{schedule}', [ScheduleController::class, 'destroy']);
        });
    });
