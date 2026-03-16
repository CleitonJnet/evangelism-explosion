<?php

use App\Http\Controllers\System\Portal\StudentDashboardController;
use App\Http\Controllers\System\Portal\StudentPortalController;
use Illuminate\Support\Facades\Route;

Route::middleware('can:access-portal-student')->prefix('student')->name('student.')->group(function () {
    Route::get('/', StudentDashboardController::class)->name('dashboard');
    Route::get('/trainings', [StudentPortalController::class, 'trainings'])->name('trainings.index');
    Route::get('/trainings/{training}', [StudentPortalController::class, 'show'])->name('trainings.show');
    Route::get('/history', [StudentPortalController::class, 'history'])->name('history');
    Route::get('/receipts', [StudentPortalController::class, 'receipts'])->name('receipts');
    Route::get('/certificates', [StudentPortalController::class, 'certificates'])->name('certificates');
});
