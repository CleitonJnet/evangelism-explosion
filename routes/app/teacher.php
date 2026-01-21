<?php

use Illuminate\Support\Facades\Route;

Route::middleware('can:access-teacher')
    ->prefix('teacher')
    ->name('teacher.')
    ->group(function () {
        Route::view('/', 'pages.app.roles.teacher.dashboard')->name('dashboard');
    });
