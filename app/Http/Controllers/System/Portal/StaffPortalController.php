<?php

namespace App\Http\Controllers\System\Portal;

use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Models\Training;
use App\Services\EventReports\StaffEventReportGovernanceService;
use App\Services\Portals\StaffAccompaniedBasesService;
use App\Services\Portals\StaffCentralInventoryService;
use App\Services\Portals\StaffCouncilService;
use App\Services\Portals\StaffPortalOverviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class StaffPortalController extends Controller
{
    public function __construct(
        private StaffPortalOverviewService $overviewService,
        private StaffEventReportGovernanceService $governanceService,
        private StaffAccompaniedBasesService $accompaniedBasesService,
        private StaffCentralInventoryService $centralInventoryService,
        private StaffCouncilService $councilService,
    ) {}

    public function basesIndex(Request $request): View
    {
        Gate::authorize('access-portal-staff');

        return view('pages.app.portal.staff.bases.index', [
            'overview' => $this->overviewService->build($request->user()),
            'basesIndex' => $this->accompaniedBasesService->buildIndex($request->user()),
        ]);
    }

    public function basesShow(Request $request, Church $church): View
    {
        Gate::authorize('access-portal-staff');
        abort_unless($this->accompaniedBasesService->canAccessChurch($request->user(), $church), 404);

        return view('pages.app.portal.staff.bases.show', [
            'overview' => $this->overviewService->build($request->user()),
            'base' => $this->accompaniedBasesService->buildShow($request->user(), $church),
        ]);
    }

    public function reportsIndex(Request $request, string $filter = 'all'): View
    {
        Gate::authorize('viewAny', \App\Models\EventReport::class);

        return view('pages.app.portal.staff.reports.index', [
            'overview' => $this->overviewService->build($request->user()),
            'reportsIndex' => $this->governanceService->buildQueue($request->user(), $filter),
        ]);
    }

    public function inventoryIndex(Request $request): View
    {
        Gate::authorize('access-portal-staff');

        return view('pages.app.portal.staff.inventory.index', [
            'overview' => $this->overviewService->build($request->user()),
            'inventory' => $this->centralInventoryService->build($request->user()),
        ]);
    }

    public function councilIndex(Request $request): View
    {
        Gate::authorize('access-portal-staff');

        return view('pages.app.portal.staff.council.index', [
            'overview' => $this->overviewService->build($request->user()),
            'council' => $this->councilService->build($request->user()),
        ]);
    }

    public function reportsShow(Request $request, Training $training): View
    {
        Gate::authorize('access-portal-staff');
        abort_unless($this->governanceService->canAccessTraining($request->user(), $training), 404);

        return view('pages.app.portal.staff.reports.show', [
            'overview' => $this->overviewService->build($request->user()),
            'comparison' => $this->governanceService->buildComparison($request->user(), $training),
            'training' => $training,
        ]);
    }
}
