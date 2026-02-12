<?php

use App\Http\Controllers\System\Mentor\OjtController;
use App\Http\Controllers\System\Mentor\OjtSessionController;
use App\Http\Controllers\System\Mentor\OjtTeamReportController;
use Illuminate\Support\Facades\Route;

Route::middleware('can:access-mentor')
    ->prefix('mentor')
    ->name('mentor.')
    ->group(function () {
        Route::view('/', 'pages.app.roles.mentor.dashboard')->name('dashboard');

        Route::prefix('ojt')->name('ojt.')->group(function () {
            Route::get('/', [OjtController::class, 'index'])->name('index');
            Route::get('sessions', [OjtSessionController::class, 'index'])->name('sessions.index');
            Route::get('sessions/{session}', [OjtSessionController::class, 'show'])->name('sessions.show');

            Route::get('teams/{team}/report', [OjtTeamReportController::class, 'create'])->name('teams.report.create');
            Route::get('teams/{team}/report/edit', [OjtTeamReportController::class, 'edit'])->name('teams.report.edit');
            Route::post('teams/{team}/report', [OjtTeamReportController::class, 'store'])->name('teams.report.store');
            Route::patch('teams/{team}/report', [OjtTeamReportController::class, 'update'])->name('teams.report.update');
            Route::post('teams/{team}/report/submit', [OjtTeamReportController::class, 'submit'])->name('teams.report.submit');
        });
    });
