<?php

namespace App\Http\Middleware;

use App\Services\Portals\PortalContextResolver;
use App\Services\Portals\PortalMenuBuilder;
use App\Services\Portals\PortalSessionManager;
use App\Services\Portals\UserPortalResolver;
use App\Support\Portals\Enums\Portal;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ResolvePortalContext
{
    public function __construct(
        protected UserPortalResolver $userPortalResolver,
        protected PortalSessionManager $portalSessionManager,
        protected PortalMenuBuilder $portalMenuBuilder,
        protected PortalContextResolver $portalContextResolver,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $currentPortal = $this->resolveCurrentPortal($request);
        $lastPortal = $this->portalSessionManager->last($request->session(), $this->userPortalResolver, $user);
        $resolvedPortals = $this->userPortalResolver->resolve($user);

        $request->attributes->set('currentPortal', $currentPortal);
        $request->attributes->set('lastPortal', $lastPortal);
        $request->attributes->set('resolvedPortals', $resolvedPortals);
        $request->attributes->set('portalMenuSections', $this->portalMenuSections($user, $currentPortal));
        $request->attributes->set('currentPortalContext', $this->portalContext($user, $currentPortal));

        View::share('currentPortal', $currentPortal);
        View::share('lastPortal', $lastPortal);
        View::share('resolvedPortals', $resolvedPortals);
        View::share('portalMenuSections', $this->portalMenuSections($user, $currentPortal));
        View::share('currentPortalContext', $this->portalContext($user, $currentPortal));

        return $next($request);
    }

    protected function resolveCurrentPortal(Request $request): ?Portal
    {
        $user = $request->user();

        if (! $user) {
            return null;
        }

        $routePortal = $this->portalFromRoute($request);

        if ($routePortal instanceof Portal) {
            abort_unless($this->userPortalResolver->canAccess($user, $routePortal), 403);

            $this->portalSessionManager->remember($request->session(), $routePortal);

            return $routePortal;
        }

        return $this->portalSessionManager->current($request->session(), $this->userPortalResolver, $user);
    }

    protected function portalFromRoute(Request $request): ?Portal
    {
        return match (true) {
            $request->routeIs('app.portal.base.*') => Portal::Base,
            $request->routeIs('app.portal.staff.*') => Portal::Staff,
            $request->routeIs('app.portal.student.*') => Portal::Student,
            default => null,
        };
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function portalMenuSections(\App\Models\User $user, ?Portal $currentPortal): array
    {
        if (! $currentPortal instanceof Portal) {
            return [];
        }

        return array_map(
            static fn ($section): array => $section->toArray(),
            $this->portalMenuBuilder->build($user, $currentPortal),
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function portalContext(\App\Models\User $user, ?Portal $currentPortal): ?array
    {
        if (! $currentPortal instanceof Portal) {
            return null;
        }

        return $this->portalContextResolver->resolve($user, $currentPortal)->toArray();
    }
}
