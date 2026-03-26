<?php

use App\Support\AccessProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])->name('app.')->group(function () {
    Route::get('start', function () {
        $user = Auth::user();
        $roleCount = $user->roles()->count();
        $lastAccessProfile = AccessProfile::rememberedRole(request(), $user);

        if ($lastAccessProfile !== null) {
            $lastAccessRoute = AccessProfile::homeRouteForRole($lastAccessProfile);

            if ($lastAccessRoute !== null) {
                return redirect()->route($lastAccessRoute);
            }
        }

        if ($roleCount === 1) {
            $roleName = $user->roles()->value('name');
            $routeName = is_string($roleName)
                ? AccessProfile::homeRouteForRole($roleName)
                : null;

            if ($routeName !== null) {
                return redirect()->route($routeName);
            }
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
