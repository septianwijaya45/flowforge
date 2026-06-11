<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

// Standalone React Router SPA (optional — enable when migrating off Inertia)
// Route::view('/app/{any?}', 'spa')->where('any', '.*')->name('spa');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
    Route::inertia('workflows', 'workflows/index')->name('workflows.index');
    Route::inertia('monitoring', 'monitoring/index')->name('monitoring.index');
    Route::inertia('schedules', 'schedules/index')->name('schedules.index');
    Route::inertia('ai/assistant', 'ai/assistant')->name('ai.assistant');
});

