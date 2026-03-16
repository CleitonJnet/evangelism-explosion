<?php

use App\Http\Controllers\System\Portal\StaffDashboardController;
use App\Http\Controllers\System\Portal\StaffPortalController;
use Illuminate\Support\Facades\Route;

Route::middleware('can:access-portal-staff')->prefix('staff')->name('staff.')->group(function () {
    Route::get('/', StaffDashboardController::class)->name('dashboard');
    Route::get('/bases', [StaffPortalController::class, 'basesIndex'])->name('bases.index');
    Route::get('/bases/{church}', [StaffPortalController::class, 'basesShow'])->name('bases.show');
    Route::get('/reports', [StaffPortalController::class, 'reportsIndex'])->name('reports.index');
    Route::get('/inventory', [StaffPortalController::class, 'inventoryIndex'])->name('inventory.index');
    Route::get('/council', [StaffPortalController::class, 'councilIndex'])->name('council.index');
    Route::get('/reports/pending', [StaffPortalController::class, 'reportsIndex'])->name('reports.pending')->defaults('filter', 'pending');
    Route::get('/reports/awaiting-review', [StaffPortalController::class, 'reportsIndex'])->name('reports.awaiting-review')->defaults('filter', 'awaiting-review');
    Route::get('/reports/follow-up', [StaffPortalController::class, 'reportsIndex'])->name('reports.follow-up')->defaults('filter', 'follow-up');
    Route::get('/trainings/{training}/reports', [StaffPortalController::class, 'reportsShow'])->name('trainings.reports');
});
