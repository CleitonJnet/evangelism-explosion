<?php

use App\Http\Controllers\System\Mentor\DashboardController;
use App\Http\Controllers\System\Mentor\OjtSessionController;
use App\Http\Controllers\System\Mentor\TrainingController;
use Illuminate\Support\Facades\Route;

Route::middleware('can:access-mentor')
    ->prefix('mentor')
    ->name('mentor.')
    ->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::get('trainings', [TrainingController::class, 'index'])->name('trainings.index');
        Route::get('trainings/{training}', [TrainingController::class, 'show'])->name('trainings.show');
        Route::get('trainings/{training}/ojt', [TrainingController::class, 'ojt'])->name('trainings.ojt');
        Route::get('ojt/sessions', [OjtSessionController::class, 'index'])->name('ojt.sessions.index');
        Route::get('ojt/sessions/{session}', [OjtSessionController::class, 'show'])->name('ojt.sessions.show');
    });
