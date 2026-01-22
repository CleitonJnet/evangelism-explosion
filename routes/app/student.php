<?php

use App\Http\Controllers\System\Student\TrainingController;
use Illuminate\Support\Facades\Route;

Route::middleware('can:access-student')->prefix('student')->name('student.')->group(function () {
    Route::view('/', 'pages.app.roles.student.dashboard')->name('dashboard');

    Route::prefix('training')->name('training.')->group(function () {
        Route::get('training', [TrainingController::class, 'index'])->name('index');
        Route::get('training/{training}', [TrainingController::class, 'show'])->name('show');
    });

});
