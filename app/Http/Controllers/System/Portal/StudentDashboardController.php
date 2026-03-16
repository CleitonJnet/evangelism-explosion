<?php

namespace App\Http\Controllers\System\Portal;

use App\Services\Portals\StudentPortalOverviewService;
use App\Support\Portals\Enums\Portal;
use Illuminate\Http\Request;

class StudentDashboardController extends PortalDashboardController
{
    protected function portal(): Portal
    {
        return Portal::Student;
    }

    protected function view(): string
    {
        return 'pages.app.portal.student.dashboard';
    }

    protected function viewData(Request $request): array
    {
        return [
            'overview' => app(StudentPortalOverviewService::class)->build($request->user()),
        ];
    }
}
