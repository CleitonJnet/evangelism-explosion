<?php

use App\Http\Controllers\System\Teacher\ChurchController;
use App\Http\Controllers\System\Teacher\CourseController;
use App\Http\Controllers\System\Teacher\InventoryController;
use App\Http\Controllers\System\Teacher\MinistryController;
use App\Http\Controllers\System\Teacher\OjtReportController;
use App\Http\Controllers\System\Teacher\OjtSessionController;
use App\Http\Controllers\System\Teacher\OjtStatsController;
use App\Http\Controllers\System\Teacher\OjtTeamController;
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
            Route::post('schedule-items', [TrainingScheduleController::class, 'storeItem'])->name('schedule-items.store');
            Route::patch('schedule-items/{item}', [TrainingScheduleController::class, 'updateItem'])->name('schedule-items.update');
            Route::delete('schedule-items/{item}', [TrainingScheduleController::class, 'destroyItem'])->name('schedule-items.destroy');
            Route::post('schedule-items/{item}/lock', [TrainingScheduleController::class, 'lock'])->name('schedule-items.lock');
            Route::post('schedule-items/{item}/unlock', [TrainingScheduleController::class, 'unlock'])->name('schedule-items.unlock');
        });

        Route::prefix('trainings/{training}/ojt')->name('trainings.ojt.')->group(function () {
            Route::get('sessions', [OjtSessionController::class, 'index'])->name('sessions.index');
            Route::get('sessions/create', [OjtSessionController::class, 'create'])->name('sessions.create');
            Route::post('sessions', [OjtSessionController::class, 'store'])->name('sessions.store');
            Route::post('sessions/generate', [OjtSessionController::class, 'generate'])->name('sessions.generate');
            Route::get('sessions/{session}/edit', [OjtSessionController::class, 'edit'])->name('sessions.edit');
            Route::patch('sessions/{session}', [OjtSessionController::class, 'update'])->name('sessions.update');
            Route::delete('sessions/{session}', [OjtSessionController::class, 'destroy'])->name('sessions.destroy');

            Route::get('teams', [OjtTeamController::class, 'index'])->name('teams.index');
            Route::post('teams/generate', [OjtTeamController::class, 'generate'])->name('teams.generate');
            Route::patch('teams/{team}/assignments', [OjtTeamController::class, 'updateAssignments'])->name('teams.assignments.update');

            Route::get('reports', [OjtReportController::class, 'index'])->name('reports.index');
            Route::get('reports/{report}', [OjtReportController::class, 'show'])->name('reports.show');

            Route::get('stats/summary', [OjtStatsController::class, 'summary'])->name('stats.summary');
            Route::get('stats/public-report', [OjtStatsController::class, 'publicReport'])->name('stats.public-report');
        });

        Route::resource('inventory', InventoryController::class)->only(['index', 'show', 'create', 'edit']);

    });
