<?php

namespace App\Http\Controllers\System\Director;

use App\Http\Controllers\Controller;
use App\Http\Requests\DirectorDashboardFilterRequest;
use App\Services\Dashboard\DirectorDashboardService;
use App\Support\Dashboard\Enums\DashboardPeriod;
use Carbon\CarbonImmutable;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(
        DirectorDashboardFilterRequest $request,
        DirectorDashboardService $dashboardService,
    ): View {
        $validated = $request->validated();
        $period = DashboardPeriod::fromValue($validated['period'] ?? null);
        $startDate = isset($validated['start_date'])
            ? CarbonImmutable::parse($validated['start_date'])->startOfDay()
            : null;
        $endDate = isset($validated['end_date'])
            ? CarbonImmutable::parse($validated['end_date'])->endOfDay()
            : ($startDate !== null ? CarbonImmutable::today()->endOfDay() : null);

        return view('pages.app.roles.director.dashboard', [
            'dashboard' => $dashboardService->build($period, $startDate, $endDate),
        ]);
    }
}
