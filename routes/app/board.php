<?php

use Illuminate\Support\Facades\Route;

Route::middleware('can:access-board')
    ->prefix('board')
    ->name('board.')
    ->group(function () {
        Route::view('/', 'pages.app.roles.board.dashboard')->name('dashboard');
    });
