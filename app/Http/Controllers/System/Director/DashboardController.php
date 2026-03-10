<?php

namespace App\Http\Controllers\System\Director;

use App\Http\Controllers\Controller;
use App\Http\Requests\DirectorDashboardFilterRequest;
use App\Services\Dashboard\DirectorDashboardService;
use App\Support\Dashboard\Enums\DashboardPeriod;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(
        DirectorDashboardFilterRequest $request,
        DirectorDashboardService $dashboardService,
    ): View {
        $period = DashboardPeriod::fromValue($request->validated('period'));

        return view('pages.app.roles.director.dashboard', [
            'dashboard' => $dashboardService->build($period),
        ]);
    }
}
