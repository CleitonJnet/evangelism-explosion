<?php

use App\Http\Controllers\System\Director\ChurchController;
use App\Http\Controllers\System\Director\CourseController;
use App\Http\Controllers\System\Director\MinistryController;
use App\Http\Controllers\System\Director\ProfileController;
use App\Http\Controllers\System\Director\TrainingController;
use App\Http\Controllers\System\Director\InventoryController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware('can:access-director')->prefix('director')->name('director.')->group(function () {
    Route::view('/', 'pages.app.roles.director.dashboard')->name('dashboard');

    Volt::route('setup', 'director.setup')->name('setup');
    
    Route::prefix('church')->name('church.')->group(function () {
        Route::get('make-host', [ChurchController::class, 'make_host'])->name('make_host');
        Route::get('view-host/{church}', [ChurchController::class, 'view_host'])->name('view_host');
        Route::get('edit-host/{church}', [ChurchController::class, 'edit_host'])->name('edit_host');
    });
    Route::resource("church",ChurchController::class)->only(['index', 'show', 'create','edit']);
        
    Route::prefix('church/{church}')->name('church.')->group(function () {
        Route::resource('profile', ProfileController::class)->only(['create','show','edit']);
    });

    Route::resource("ministry",MinistryController::class)->only(['index', 'show', 'create','edit']);
    Route::prefix('ministry/{ministry}')->name('ministry.')->group(function () {
        Route::resource('course', CourseController::class)->only(['create','show','edit']);
    });

    Route::resource("training",TrainingController::class)->only(['index', 'show', 'create','edit']);

    Route::resource("inventory",InventoryController::class)->only(['index', 'show', 'create','edit']);
});
