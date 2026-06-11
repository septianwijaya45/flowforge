<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Modules\Workflow\Models\Workflow;
use Modules\WorkflowEngine\Models\WorkflowRun;

Route::inertia('/', 'welcome')->name('home');

// Standalone React Router SPA (optional — enable when migrating off Inertia)
// Route::view('/app/{any?}', 'spa')->where('any', '.*')->name('spa');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
    Route::inertia('workflows', 'workflows/index')->name('workflows.index');
    Route::get('workflows/{workflow}/builder', function (Workflow $workflow) {
        return Inertia::render('workflows/builder', [
            'workflowId' => $workflow->id,
            'workflowName' => $workflow->name,
        ]);
    })->name('workflows.builder');
    Route::inertia('monitoring', 'monitoring/index')->name('monitoring.index');
    Route::get('monitoring/runs/{run}', function (WorkflowRun $run) {
        return Inertia::render('monitoring/runs/show', [
            'runId' => $run->id,
        ]);
    })->name('monitoring.runs.show');
    Route::inertia('schedules', 'schedules/index')->name('schedules.index');
    Route::inertia('ai/assistant', 'ai/assistant')->name('ai.assistant');
});

