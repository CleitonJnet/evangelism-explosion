<?php

namespace App\Http\Controllers\System\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\TrainingIndexFilterRequest;
use App\Models\Training;
use App\Services\EventReports\EventReportWorkflowService;
use App\Services\Inventory\BaseInventoryPortalService;
use App\Services\Portals\BasePortalNavigationService;
use App\Services\Portals\BasePortalOverviewService;
use App\Services\Portals\PortalBaseCapabilityService;
use App\Services\Training\TrainingIndexService;
use App\Support\TrainingAccess\TrainingCapabilityResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BasePortalController extends Controller
{
    public function __construct(
        private BasePortalOverviewService $overviewService,
        private TrainingIndexService $trainingIndexService,
        private BaseInventoryPortalService $baseInventoryPortalService,
        private TrainingCapabilityResolver $trainingCapabilityResolver,
        private PortalBaseCapabilityService $portalBaseCapabilityService,
        private BasePortalNavigationService $basePortalNavigationService,
        private EventReportWorkflowService $eventReportWorkflowService,
    ) {}

    public function myBase(Request $request): View
    {
        Gate::authorize('viewBaseOverview');
        abort_unless($this->basePortalNavigationService->canViewMyBase($request->user()), 403);

        return view('pages.app.portal.base.my-base', [
            'overview' => $this->overviewService->build($request->user()),
            'baseCapabilities' => $this->portalBaseCapabilityService->baseSummary($request->user()),
            'navigation' => $this->basePortalNavigationService->summary($request->user()),
        ]);
    }

    public function serving(TrainingIndexFilterRequest $request, string $status = 'scheduled'): View
    {
        abort_unless($this->basePortalNavigationService->canViewServing($request->user()), 403);

        return view('pages.app.portal.base.serving', [
            'overview' => $this->overviewService->build($request->user()),
            'baseCapabilities' => $this->portalBaseCapabilityService->baseSummary($request->user()),
            'navigation' => $this->basePortalNavigationService->summary($request->user()),
            ...$this->trainingIndexService->buildIndexData(
                $request->user(),
                $status,
                $request->filterTerm(),
                [
                    'planning' => 'app.portal.base.serving.planning',
                    'scheduled' => 'app.portal.base.serving.scheduled',
                    'canceled' => 'app.portal.base.serving.canceled',
                    'completed' => 'app.portal.base.serving.completed',
                ],
                'serving',
                $request->filters(),
            ),
        ]);
    }

    public function events(Request $request): View
    {
        Gate::authorize('viewBaseOverview');
        abort_unless($this->basePortalNavigationService->canViewBaseEvents($request->user()), 403);

        return view('pages.app.portal.base.events', [
            'overview' => $this->overviewService->build($request->user()),
            'baseCapabilities' => $this->portalBaseCapabilityService->baseSummary($request->user()),
            'navigation' => $this->basePortalNavigationService->summary($request->user()),
        ]);
    }

    public function inventory(Request $request): View
    {
        Gate::authorize('viewBaseInventory');
        abort_unless($this->basePortalNavigationService->canViewBaseInventory($request->user()), 403);

        return view('pages.app.portal.base.inventory', [
            'overview' => $this->overviewService->build($request->user()),
            'baseCapabilities' => $this->portalBaseCapabilityService->baseSummary($request->user()),
            'navigation' => $this->basePortalNavigationService->summary($request->user()),
            'inventory' => $this->baseInventoryPortalService->build($request->user()),
        ]);
    }

    public function legacyContext(Request $request, Training $training): RedirectResponse
    {
        $this->authorize('viewBaseContext', $training);

        return redirect()->route('app.portal.base.trainings.show', $training);
    }

    public function showTraining(Request $request, Training $training): View
    {
        $this->authorize('viewBaseOverview', $training);

        return view('pages.app.portal.base.trainings.show', $this->trainingPageData($request, $training, 'show'));
    }

    public function registrations(Request $request, Training $training): View
    {
        $this->authorize('manageTrainingRegistrationsBaseContext', $training);

        return view('pages.app.portal.base.trainings.registrations', $this->trainingPageData($request, $training, 'registrations'));
    }

    public function preparation(Request $request, Training $training): View
    {
        $this->authorize('viewBaseOverview', $training);

        return view('pages.app.portal.base.trainings.preparation', $this->trainingPageData($request, $training, 'preparation'));
    }

    public function schedule(Request $request, Training $training): View
    {
        $this->authorize('viewBaseOverview', $training);

        return view('pages.app.portal.base.trainings.schedule', $this->trainingPageData($request, $training, 'schedule'));
    }

    public function materials(Request $request, Training $training): View
    {
        $this->authorize('viewEventMaterialsBaseContext', $training);

        return view('pages.app.portal.base.trainings.materials', $this->trainingPageData($request, $training, 'materials'));
    }

    public function statistics(Request $request, Training $training): View
    {
        $this->authorize('manageMentorsBaseContext', $training);

        return view('pages.app.portal.base.trainings.statistics', $this->trainingPageData($request, $training, 'statistics'));
    }

    public function reports(Request $request, Training $training): View
    {
        $this->authorize('viewBaseOverview', $training);

        return view('pages.app.portal.base.trainings.reports', $this->trainingPageData($request, $training, 'reports'));
    }

    public function stpBoard(Request $request, Training $training): View
    {
        $capabilities = $this->portalBaseCapabilityService->eventSummary($request->user(), $training);

        abort_unless($capabilities['viewServedTrainings'] && $capabilities['manageMentors'], 403);

        return view('pages.app.portal.base.trainings.stp-approaches', $this->trainingPageData($request, $training, 'stp'));
    }

    /**
     * @return array<string, mixed>
     */
    private function trainingPageData(Request $request, Training $training, string $activeTab): array
    {
        $this->authorize('viewBaseContext', $training);

        $training->loadMissing([
            'course.ministry',
            'church',
            'teacher',
            'assistantTeachers',
            'mentors',
            'churchEventReport.sections',
            'churchEventReport.reviews.reviewer',
            'teacherEventReport.sections',
            'teacherEventReport.reviews.reviewer',
            'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
        ]);

        $user = $request->user();
        $overview = $this->overviewService->build($user);
        $portalCapabilities = $this->portalBaseCapabilityService->eventSummary($user, $training);
        $legacyCapabilities = $this->trainingCapabilityResolver->summaryForBaseContext($user, $training);
        $assignments = $this->trainingCapabilityResolver->baseAssignments($user, $training);

        return [
            'overview' => $overview,
            'training' => $training,
            'portalCapabilities' => $portalCapabilities,
            'capabilities' => $legacyCapabilities,
            'assignments' => $assignments,
            'activeTab' => $activeTab,
            'trainingContext' => $this->trainingContextLabel($user, $training),
            'portalLabel' => 'Portal Base e Treinamentos',
            'portalRoles' => $user->roles()
                ->whereIn('name', ['Teacher', 'Mentor', 'Facilitator', 'FieldWorker', 'Director'])
                ->pluck('name')
                ->values()
                ->all(),
            'reportSummary' => $this->eventReportWorkflowService->buildTrainingSummary($training),
            'tabs' => $this->basePortalNavigationService->eventTabs($training, $portalCapabilities),
            'areaCards' => $this->basePortalNavigationService->eventAreaCards($training, $portalCapabilities),
        ];
    }

    private function trainingContextLabel(\App\Models\User $user, Training $training): string
    {
        $servesTraining = $this->trainingCapabilityResolver->canViewAsServingContext($user, $training);
        $isHostedByBase = (int) $user->church_id !== 0 && (int) $user->church_id === (int) $training->church_id;

        return match (true) {
            $servesTraining && $isHostedByBase => 'Evento sediado pela sua base e vinculado a sua atuacao',
            $servesTraining => 'Treinamento em que voce serve',
            $isHostedByBase => 'Evento sediado pela sua igreja-base',
            default => 'Contexto do Portal Base',
        };
    }
}
