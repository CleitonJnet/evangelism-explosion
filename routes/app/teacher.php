<?php

use App\Http\Controllers\System\Teacher\ChurchController;
use App\Http\Controllers\System\Teacher\DashboardController;
use App\Http\Controllers\System\Teacher\OjtController;
use App\Http\Controllers\System\Teacher\StpApproachController;
use App\Http\Controllers\System\Teacher\TrainingController;
use App\Http\Middleware\ShowScheduleAttentionModal;
use Illuminate\Support\Facades\Route;

Route::middleware('can:access-teacher')->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::middleware('can:manageChurches')->prefix('churches')->name('church.')->group(function () {
        Route::get('make-host', [ChurchController::class, 'make_host'])->name('make_host');
        Route::get('view-host/{church}', [ChurchController::class, 'view_host'])->name('view_host');
        Route::get('edit-host/{church}', [ChurchController::class, 'edit_host'])->name('edit_host');
    });
    Route::middleware('can:manageChurches')->resource('churches', ChurchController::class)->only(['index', 'show', 'create', 'edit']);

    Route::get('trainings/planning', [TrainingController::class, 'indexByStatus'])->name('trainings.planning')->defaults('status', 'planning');
    Route::get('trainings/scheduled', [TrainingController::class, 'indexByStatus'])->name('trainings.scheduled')->defaults('status', 'scheduled');
    Route::get('trainings/canceled', [TrainingController::class, 'indexByStatus'])->name('trainings.canceled')->defaults('status', 'canceled');
    Route::get('trainings/completed', [TrainingController::class, 'indexByStatus'])->name('trainings.completed')->defaults('status', 'completed');

    Route::get('trainings/{training}/registrations', [TrainingController::class, 'registrations'])->name('trainings.registrations');
    Route::get('trainings/{training}/schedule', [TrainingController::class, 'schedule'])->middleware(ShowScheduleAttentionModal::class)->name('trainings.schedule');
    Route::get('trainings/{training}/statistics', [OjtController::class, 'statistics'])->name('trainings.statistics');
    Route::get('trainings/{training}/stp/approaches', [StpApproachController::class, 'board'])->name('trainings.stp.approaches');
    Route::get('trainings/{training}/testimony', [TrainingController::class, 'testimony'])->name('trainings.testimony');
    Route::put('trainings/{training}/testimony', [TrainingController::class, 'updateTestimony'])->name('trainings.testimony.update');
    Route::resource('trainings', TrainingController::class)->only(['index', 'show', 'create', 'destroy']);
});
