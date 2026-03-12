<?php

namespace App\Http\Controllers\System\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\TeacherDashboardFilterRequest;
use App\Services\Dashboard\TeacherDashboardService;
use App\Support\Dashboard\Enums\DashboardPeriod;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(TeacherDashboardFilterRequest $request, TeacherDashboardService $dashboardService): View
    {
        $validated = $request->validated();
        $period = DashboardPeriod::fromValue($validated['period'] ?? null);
        $startDate = isset($validated['start_date'])
            ? CarbonImmutable::parse($validated['start_date'])->startOfDay()
            : null;
        $endDate = isset($validated['end_date'])
            ? CarbonImmutable::parse($validated['end_date'])->endOfDay()
            : ($startDate !== null ? CarbonImmutable::today()->endOfDay() : null);

        return view('pages.app.roles.teacher.dashboard', [
            'dashboard' => $dashboardService->build(Auth::user(), $period, $startDate, $endDate),
        ]);
    }
}
