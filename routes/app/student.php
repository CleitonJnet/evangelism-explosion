<?php

use Illuminate\Support\Facades\Route;

Route::middleware('can:access-student')
    ->prefix('student')
    ->name('student.')
    ->group(function () {
        Route::view('/', 'pages.app.roles.student.dashboard')->name('dashboard');
    });
