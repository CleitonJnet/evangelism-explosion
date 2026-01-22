<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])->name('app.')->group(function () {
    Route::get('start', function () {
        $user = Auth::user();
        $roleCount = $user->roles()->count();

        if ($roleCount === 1) {
            $roleName = $user->roles()->value('name');

            $routeName = match ($roleName) {
                'Board' => 'app.board.dashboard',
                'Director' => 'app.director.dashboard',
                'FieldWorker' => 'app.fieldworker.dashboard',
                'Teacher' => 'app.teacher.dashboard',
                'Facilitator' => 'app.facilitator.dashboard',
                'Mentor' => 'app.mentor.dashboard',
                'Student' => 'app.student.dashboard',
                default => 'app.start',
            };

            return redirect()->route($routeName);
        }

        return view('pages.app.start');
    })->name('start');

    require __DIR__.'/board.php';
    require __DIR__.'/director.php';
    require __DIR__.'/facilitator.php';
    require __DIR__.'/fieldworker.php';
    require __DIR__.'/mentor.php';
    require __DIR__.'/settings.php';
    require __DIR__.'/student.php';
    require __DIR__.'/teacher.php';
});
