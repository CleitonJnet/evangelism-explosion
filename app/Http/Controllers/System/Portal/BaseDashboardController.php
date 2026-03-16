<?php

namespace App\Http\Controllers\System\Portal;

use App\Services\Portals\BasePortalNavigationService;
use App\Services\Portals\BasePortalOverviewService;
use App\Support\Portals\Enums\Portal;
use Illuminate\Http\Request;

class BaseDashboardController extends PortalDashboardController
{
    protected function portal(): Portal
    {
        return Portal::Base;
    }

    protected function view(): string
    {
        return 'pages.app.portal.base.dashboard';
    }

    protected function viewData(Request $request): array
    {
        return [
            'overview' => app(BasePortalOverviewService::class)->build($request->user()),
            'navigation' => app(BasePortalNavigationService::class)->summary($request->user()),
        ];
    }
}
