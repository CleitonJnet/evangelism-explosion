<?php

namespace App\Http\Responses;

use App\Services\Portals\PortalSessionManager;
use App\Services\Portals\UserPortalResolver;
use App\Support\Portals\Enums\Portal;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function __construct(
        private PortalSessionManager $portalSessionManager,
        private UserPortalResolver $userPortalResolver,
    ) {}

    public function toResponse($request): RedirectResponse
    {
        $portal = Portal::tryFrom((string) $request->input('portal'));
        $user = $request->user();

        if ($portal instanceof Portal && $user && $this->userPortalResolver->canAccess($user, $portal)) {
            $this->portalSessionManager->remember($request->session(), $portal);

            return redirect()->route($portal->entryRoute());
        }

        return redirect()->intended(config('fortify.home'));
    }
}
