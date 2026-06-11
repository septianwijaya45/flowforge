<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\AI\Http\Controllers\NaturalLanguageWorkflowController;
use Modules\Tenant\Http\Middleware\EnsureTenantContext;

Route::prefix('ai')
    ->middleware(['auth:web,api', EnsureTenantContext::class])
    ->group(function (): void {
        Route::middleware('role:admin,editor')->group(function (): void {
            Route::post('workflows/build', [NaturalLanguageWorkflowController::class, 'build']);
        });
    });
