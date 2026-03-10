<?php

namespace App\Http\Controllers\System\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\TeacherDashboardFilterRequest;
use App\Services\Dashboard\TeacherDashboardService;
use App\Support\Dashboard\Enums\DashboardPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(TeacherDashboardFilterRequest $request, TeacherDashboardService $dashboardService): View
    {
        $period = DashboardPeriod::fromValue($request->validated('period'));

        return view('pages.app.roles.teacher.dashboard', [
            'dashboard' => $dashboardService->build(Auth::user(), $period),
        ]);
    }
}
