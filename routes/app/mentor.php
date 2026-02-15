<?php

use Illuminate\Support\Facades\Route;

Route::middleware('can:access-mentor')
    ->prefix('mentor')
    ->name('mentor.')
    ->group(function () {
        Route::view('/', 'pages.app.roles.mentor.dashboard')->name('dashboard');

    });
