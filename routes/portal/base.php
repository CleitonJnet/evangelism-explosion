<?php

use App\Http\Controllers\System\Portal\BaseDashboardController;
use App\Http\Controllers\System\Portal\BasePortalController;
use Illuminate\Support\Facades\Route;

Route::middleware('can:access-portal-base')->prefix('base')->name('base.')->group(function () {
    Route::get('/', BaseDashboardController::class)->name('dashboard');
    Route::get('/my-base', [BasePortalController::class, 'myBase'])->name('my-base');
    Route::get('/inventory', [BasePortalController::class, 'inventory'])->name('inventory');
    Route::get('/serving', [BasePortalController::class, 'serving'])->name('serving');
    Route::get('/serving/planning', [BasePortalController::class, 'serving'])->name('serving.planning')->defaults('status', 'planning');
    Route::get('/serving/scheduled', [BasePortalController::class, 'serving'])->name('serving.scheduled')->defaults('status', 'scheduled');
    Route::get('/serving/canceled', [BasePortalController::class, 'serving'])->name('serving.canceled')->defaults('status', 'canceled');
    Route::get('/serving/completed', [BasePortalController::class, 'serving'])->name('serving.completed')->defaults('status', 'completed');
    Route::get('/events', [BasePortalController::class, 'events'])->name('events');
    Route::get('/trainings/{training}/context', [BasePortalController::class, 'legacyContext'])->name('trainings.context');
    Route::get('/trainings/{training}', [BasePortalController::class, 'showTraining'])->name('trainings.show');
    Route::get('/trainings/{training}/registrations', [BasePortalController::class, 'registrations'])->name('trainings.registrations');
    Route::get('/trainings/{training}/preparation', [BasePortalController::class, 'preparation'])->name('trainings.preparation');
    Route::get('/trainings/{training}/schedule', [BasePortalController::class, 'schedule'])->name('trainings.schedule');
    Route::get('/trainings/{training}/materials', [BasePortalController::class, 'materials'])->name('trainings.materials');
    Route::get('/trainings/{training}/statistics', [BasePortalController::class, 'statistics'])->name('trainings.statistics');
    Route::get('/trainings/{training}/reports', [BasePortalController::class, 'reports'])->name('trainings.reports');
    Route::get('/trainings/{training}/stp/approaches', [BasePortalController::class, 'stpBoard'])->name('trainings.stp.approaches');
});
