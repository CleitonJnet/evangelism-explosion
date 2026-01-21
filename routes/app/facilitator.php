<?php

use Illuminate\Support\Facades\Route;

Route::middleware('can:access-facilitator')
    ->prefix('facilitator')
    ->name('facilitator.')
    ->group(function () {
        Route::view('/', 'pages.app.roles.facilitator.dashboard')->name('dashboard');
    });
