<?php

namespace App\Http\Controllers\System\Portal;

use App\Http\Controllers\Controller;
use App\Services\Portals\PortalContextResolver;
use App\Services\Portals\PortalMenuBuilder;
use App\Services\Portals\UserPortalResolver;
use App\Support\Portals\Enums\Portal;
use Illuminate\Http\Request;
use Illuminate\View\View;

abstract class PortalDashboardController extends Controller
{
    public function __invoke(
        Request $request,
        UserPortalResolver $userPortalResolver,
        PortalContextResolver $portalContextResolver,
        PortalMenuBuilder $portalMenuBuilder,
    ): View {
        $user = $request->user();
        $portal = $this->portal();

        return view($this->view(), [
            'portal' => $portal,
            'resolvedPortals' => array_map(
                static fn ($item): array => $item->toArray(),
                $userPortalResolver->resolve($user),
            ),
            'suggestedPortal' => $userPortalResolver->suggestedDefault($user),
            'portalContext' => $portalContextResolver->resolve($user, $portal)->toArray(),
            'menuSections' => array_map(
                static fn ($section): array => $section->toArray(),
                $portalMenuBuilder->build($user, $portal),
            ),
            ...$this->viewData($request),
        ]);
    }

    protected function view(): string
    {
        return 'pages.app.portal.dashboard';
    }

    /**
     * @return array<string, mixed>
     */
    protected function viewData(Request $request): array
    {
        return [];
    }

    abstract protected function portal(): Portal;
}
