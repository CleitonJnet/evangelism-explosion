<?php

use App\Http\Controllers\System\Teacher\ChurchController;
use App\Http\Controllers\System\Teacher\CourseController;
use App\Http\Controllers\System\Teacher\InventoryController;
use App\Http\Controllers\System\Teacher\MinistryController;
use App\Http\Controllers\System\Teacher\ProfileController;
use App\Http\Controllers\System\Teacher\TrainingController;
use App\Http\Controllers\System\Teacher\TrainingScheduleController;
use Illuminate\Support\Facades\Route;

Route::middleware('can:access-teacher')
    ->prefix('teacher')
    ->name('teacher.')
    ->group(function () {
        Route::view('/', 'pages.app.roles.teacher.dashboard')->name('dashboard');

        Route::prefix('church')->name('church.')->group(function () {
            Route::get('make-host', [ChurchController::class, 'make_host'])->name('make_host');
            Route::get('view-host/{church}', [ChurchController::class, 'view_host'])->name('view_host');
            Route::get('edit-host/{church}', [ChurchController::class, 'edit_host'])->name('edit_host');
        });
        Route::resource('church', ChurchController::class)->only(['index', 'show', 'create', 'edit']);

        Route::prefix('church/{church}')->name('church.')->group(function () {
            Route::resource('profile', ProfileController::class)->only(['create', 'show', 'edit']);
        });

        Route::resource('ministry', MinistryController::class)->only(['index', 'show', 'create', 'edit']);
        Route::prefix('ministry/{ministry}')->name('ministry.')->group(function () {
            Route::resource('course', CourseController::class)->only(['create', 'show', 'edit']);
        });

        Route::get('training/planning', [TrainingController::class, 'indexByStatus'])->name('training.planning')->defaults('status', 'planning');
        Route::get('training/scheduled', [TrainingController::class, 'indexByStatus'])->name('training.scheduled')->defaults('status', 'scheduled');
        Route::get('training/canceled', [TrainingController::class, 'indexByStatus'])->name('training.canceled')->defaults('status', 'canceled');
        Route::get('training/completed', [TrainingController::class, 'indexByStatus'])->name('training.completed')->defaults('status', 'completed');

        Route::get('training/{training}/schedule', [TrainingController::class, 'schedule'])->name('training.schedule');
        Route::resource('training', TrainingController::class)->only(['index', 'show', 'create', 'edit', 'destroy']);

        Route::prefix('trainings/{training}')->name('trainings.')->group(function () {
            Route::post('schedule/regenerate', [TrainingScheduleController::class, 'regenerate'])->name('schedule.regenerate');
            Route::patch('schedule-items/{item}', [TrainingScheduleController::class, 'updateItem'])->name('schedule-items.update');
            Route::post('schedule-items/{item}/lock', [TrainingScheduleController::class, 'lock'])->name('schedule-items.lock');
            Route::post('schedule-items/{item}/unlock', [TrainingScheduleController::class, 'unlock'])->name('schedule-items.unlock');
        });

        Route::resource('inventory', InventoryController::class)->only(['index', 'show', 'create', 'edit']);

    });
