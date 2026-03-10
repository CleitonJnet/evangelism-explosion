<?php

use App\Http\Controllers\System\Director\ChurchController;
use App\Http\Controllers\System\Director\CourseController;
use App\Http\Controllers\System\Director\InventoryController;
use App\Http\Controllers\System\Director\MinistryController;
use App\Http\Controllers\System\Director\OjtController;
use App\Http\Controllers\System\Director\ProfileController;
use App\Http\Controllers\System\Director\SiteController;
use App\Http\Controllers\System\Director\StpApproachController;
use App\Http\Controllers\System\Director\TrainingController;
use App\Http\Controllers\TrainingScheduleController;
use App\Http\Middleware\ShowScheduleAttentionModal;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware('can:access-director')->prefix('director')->name('director.')->group(function () {
    Route::view('/', 'pages.app.roles.director.dashboard')->name('dashboard');

    Volt::route('setup', 'pages.app.director.setup')->name('setup');

    Route::middleware('can:manageChurches')->prefix('church')->name('church.')->group(function () {
        Route::get('make-host', [ChurchController::class, 'make_host'])->name('make_host');
        Route::get('view-host/{church}', [ChurchController::class, 'view_host'])->name('view_host');
        Route::get('edit-host/{church}', [ChurchController::class, 'edit_host'])->name('edit_host');
        Route::get('profiles/{user}', [ChurchController::class, 'profile'])->name('profiles.show');
    });
    Route::middleware('can:manageChurches')->resource('church', ChurchController::class)->only(['index', 'show', 'create', 'edit']);

    Route::middleware('can:manageChurches')->prefix('church/{church}')->name('church.')->group(function () {
        Route::resource('profile', ProfileController::class)->only(['create', 'show', 'edit']);
    });

    Route::resource('ministry', MinistryController::class)->only(['index', 'show', 'create', 'edit']);
    Route::prefix('ministry/{ministry}')->name('ministry.')->group(function () {
        Route::get('course/{course}/sections', [CourseController::class, 'sections'])->name('course.sections');
        Route::resource('course', CourseController::class)->only(['create', 'show', 'edit']);
    });

    Route::get('training/planning', [TrainingController::class, 'indexByStatus'])->name('training.planning')->defaults('status', 'planning');
    Route::get('training/scheduled', [TrainingController::class, 'indexByStatus'])->name('training.scheduled')->defaults('status', 'scheduled');
    Route::get('training/canceled', [TrainingController::class, 'indexByStatus'])->name('training.canceled')->defaults('status', 'canceled');
    Route::get('training/completed', [TrainingController::class, 'indexByStatus'])->name('training.completed')->defaults('status', 'completed');

    Route::get('training/{training}/registrations', [TrainingController::class, 'registrations'])->name('training.registrations');
    Route::get('training/{training}/schedule', [TrainingController::class, 'schedule'])->middleware(ShowScheduleAttentionModal::class)->name('training.schedule');
    Route::get('training/{training}/statistics', [OjtController::class, 'statistics'])->name('training.statistics');
    Route::get('training/{training}/stp/approaches', [StpApproachController::class, 'board'])->name('training.stp.approaches');
    Route::get('training/{training}/testimony', [TrainingController::class, 'testimony'])->name('training.testimony');
    Route::put('training/{training}/testimony', [TrainingController::class, 'updateTestimony'])->name('training.testimony.update');

    Route::resource('training', TrainingController::class)->only(['index', 'show', 'create', 'edit', 'destroy']);

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
    Route::resource('trainings', TrainingController::class)
        ->only(['index', 'show', 'create', 'edit', 'destroy'])
        ->names([
            'index' => 'trainings.index',
            'show' => 'trainings.show',
            'create' => 'trainings.create',
            'edit' => 'trainings.edit',
            'destroy' => 'trainings.destroy',
        ]);

    Route::prefix('trainings/{training}')->name('trainings.')->group(function () {
        Route::post('schedule/regenerate', [TrainingScheduleController::class, 'regenerate'])->name('schedule.regenerate');
        Route::post('schedule-items', [TrainingScheduleController::class, 'storeItem'])->name('schedule-items.store');
        Route::patch('schedule-items/{item}', [TrainingScheduleController::class, 'updateItem'])->name('schedule-items.update');
        Route::delete('schedule-items/{item}', [TrainingScheduleController::class, 'destroyItem'])->name('schedule-items.destroy');
    });

    Route::resource('inventory', InventoryController::class)->only(['index', 'show', 'create', 'edit']);
    Route::get('testimonials', [SiteController::class, 'testimonials'])->name('testimonials');
});
