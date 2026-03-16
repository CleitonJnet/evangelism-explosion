<?php

namespace App\Services\Portals;

use App\Models\User;
use App\Support\Portals\Enums\Portal;
use Illuminate\Contracts\Session\Session;

class PortalSessionManager
{
    public const CURRENT_PORTAL_SESSION_KEY = 'portal.current';

    public const LAST_PORTAL_SESSION_KEY = 'portal.last';

    public function current(Session $session, UserPortalResolver $userPortalResolver, User $user): ?Portal
    {
        $currentPortal = $this->portalFromSession(
            $session->get(self::CURRENT_PORTAL_SESSION_KEY),
            $userPortalResolver,
            $user,
        );

        if ($currentPortal instanceof Portal) {
            return $currentPortal;
        }

        $lastPortal = $this->last($session, $userPortalResolver, $user);

        if ($lastPortal instanceof Portal) {
            return $lastPortal;
        }

        $resolvedPortals = $userPortalResolver->resolve($user);

        if (count($resolvedPortals) === 1) {
            return $resolvedPortals[0]->portal;
        }

        return null;
    }

    public function last(Session $session, UserPortalResolver $userPortalResolver, User $user): ?Portal
    {
        return $this->portalFromSession(
            $session->get(self::LAST_PORTAL_SESSION_KEY),
            $userPortalResolver,
            $user,
        );
    }

    public function remember(Session $session, Portal $portal): void
    {
        $session->put(self::CURRENT_PORTAL_SESSION_KEY, $portal->value);
        $session->put(self::LAST_PORTAL_SESSION_KEY, $portal->value);
    }

    public function forget(Session $session): void
    {
        $session->forget([
            self::CURRENT_PORTAL_SESSION_KEY,
            self::LAST_PORTAL_SESSION_KEY,
        ]);
    }

    protected function portalFromSession(
        mixed $value,
        UserPortalResolver $userPortalResolver,
        User $user,
    ): ?Portal {
        if (! is_string($value)) {
            return null;
        }

        $portal = Portal::tryFrom($value);

        if (! $portal instanceof Portal) {
            return null;
        }

        if (! $userPortalResolver->canAccess($user, $portal)) {
            return null;
        }

        return $portal;
    }
}
