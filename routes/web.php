<?php

use App\Http\Controllers\Auth\ForcePasswordChangeController;
use App\Http\Controllers\Web\PublicEventScheduleController;
use App\Http\Controllers\Web\SiteController;
use Illuminate\Support\Facades\Route;

Route::name('web.')->group(function () {
    Route::get('/', [SiteController::class, 'home'])->name('home');
    Route::get('donate', [SiteController::class, 'donate'])->name('donate');

    Route::prefix('ministry')->name('ministry.')->group(function () {
        Route::get('everyday-evangelism', [SiteController::class, 'everyday_evangelism'])->name('everyday-evangelism');
        Route::get('kids-ee', [SiteController::class, 'kids_ee'])->name('kids-ee');
        Route::get('kids-ee2', [SiteController::class, 'kids_ee2'])->name('kids-ee2');
    });

    Route::prefix('about-ee')->name('about.')->group(function () {
        Route::get('history', [SiteController::class, 'history'])->name('history');
        Route::get('faith', [SiteController::class, 'faith'])->name('faith');
        Route::get('vision-mission', [SiteController::class, 'vision_mission'])->name('vision-mission');
    });

    Route::prefix('event')->name('event.')->group(function () {
        Route::get('schedule', [SiteController::class, 'schedule'])->name('schedule-request');
        Route::get('list', [SiteController::class, 'events'])->name('index');
        Route::get('{training}/programacao', [PublicEventScheduleController::class, 'show'])->name('schedule');
        Route::get('{training}/programacao/pdf', [PublicEventScheduleController::class, 'pdf'])->name('schedule.pdf');
        Route::get('{id}/banner-download', [SiteController::class, 'downloadBanner'])->name('banner.download');
        Route::get('{id}/details', [SiteController::class, 'details'])->name('details');
        Route::get('{id}/register', [SiteController::class, 'register'])->name('register');
        Route::get('{id}/login', [SiteController::class, 'login'])->name('login');
        Route::get('training-host-church', [SiteController::class, 'clinic_base'])->name('clinic-base');
    });
});

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('force-password-change', [ForcePasswordChangeController::class, 'edit'])
        ->name('force-password-change.show');
    Route::put('force-password-change', [ForcePasswordChangeController::class, 'update'])
        ->name('force-password-change.update');
});

require __DIR__.'/app/start.php';
