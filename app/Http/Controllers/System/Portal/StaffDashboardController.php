<?php

namespace App\Http\Controllers\System\Portal;

use App\Services\Portals\StaffPortalOverviewService;
use App\Support\Portals\Enums\Portal;
use Illuminate\Http\Request;

class StaffDashboardController extends PortalDashboardController
{
    protected function portal(): Portal
    {
        return Portal::Staff;
    }

    protected function view(): string
    {
        return 'pages.app.portal.staff.dashboard';
    }

    protected function viewData(Request $request): array
    {
        return [
            'overview' => app(StaffPortalOverviewService::class)->build($request->user()),
        ];
    }
}
