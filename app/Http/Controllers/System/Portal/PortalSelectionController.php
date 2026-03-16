<?php

namespace App\Http\Controllers\System\Portal;

use App\Http\Controllers\Controller;
use App\Services\Portals\PortalSessionManager;
use App\Services\Portals\UserPortalResolver;
use App\Support\Portals\Enums\Portal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PortalSelectionController extends Controller
{
    public function __invoke(
        Request $request,
        Portal $portal,
        UserPortalResolver $userPortalResolver,
        PortalSessionManager $portalSessionManager,
    ): RedirectResponse {
        $user = $request->user();

        if (! $user || ! $userPortalResolver->canAccess($user, $portal)) {
            abort(403);
        }

        $portalSessionManager->remember($request->session(), $portal);

        return redirect()->route($portal->entryRoute());
    }
}
