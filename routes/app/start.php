<?php

use App\Http\Controllers\System\Portal\PortalSelectionController;
use App\Services\Portals\PortalSessionManager;
use App\Services\Portals\UserPortalResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])->name('app.')->group(function () {
    Route::get('start', function (
        Request $request,
        UserPortalResolver $userPortalResolver,
        PortalSessionManager $portalSessionManager,
    ) {
        $user = $request->user();
        $resolvedPortals = $userPortalResolver->resolve($user);

        if (count($resolvedPortals) === 1) {
            $portal = $resolvedPortals[0]->portal;

            $portalSessionManager->remember($request->session(), $portal);

            return redirect()->route($portal->entryRoute());
        }

        return view('pages.app.start', [
            'resolvedPortals' => array_map(
                static fn ($resolvedPortal): array => $resolvedPortal->toArray(),
                $resolvedPortals,
            ),
            'suggestedPortal' => $userPortalResolver->suggestedDefault($user),
            'currentPortal' => $portalSessionManager->current($request->session(), $userPortalResolver, $user),
            'lastPortal' => $portalSessionManager->last($request->session(), $userPortalResolver, $user),
        ]);
    })->name('start');

    Route::post('start/portal/{portal}', PortalSelectionController::class)->name('portal.select');

    Route::prefix('portal')->name('portal.')->group(function () {
        require __DIR__.'/../portal/base.php';
        require __DIR__.'/../portal/staff.php';
        require __DIR__.'/../portal/student.php';
    });

    require __DIR__.'/board.php';
    require __DIR__.'/director.php';
    require __DIR__.'/facilitator.php';
    require __DIR__.'/fieldworker.php';
    require __DIR__.'/mentor.php';
    require __DIR__.'/settings.php';
    require __DIR__.'/student.php';
    require __DIR__.'/teacher.php';
});
