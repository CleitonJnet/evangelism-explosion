<?php

use Illuminate\Support\Facades\Route;

Route::middleware('can:access-fieldworker')
    ->prefix('fieldworker')
    ->name('fieldworker.')
    ->group(function () {
        Route::view('/', 'pages.app.roles.fieldworker.dashboard')->name('dashboard');
    });
