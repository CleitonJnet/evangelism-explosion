<?php

namespace App\Services\Portals;

use App\Helpers\DayScheduleHelper;
use App\Models\Inventory;
use App\Models\Training;
use App\Models\User;
use App\Services\Dashboard\DirectorDashboardService;
use App\Services\Dashboard\TeacherDashboardService;
use App\Services\Training\MentorTrainingOverviewService;
use App\Support\Dashboard\Enums\DashboardPeriod;
use App\Support\TrainingAccess\TrainingCapabilityResolver;
use App\Support\TrainingAccess\TrainingVisibilityScope;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BasePortalOverviewService
{
    public function __construct(
        private TeacherDashboardService $teacherDashboardService,
        private DirectorDashboardService $directorDashboardService,
        private MentorTrainingOverviewService $mentorTrainingOverviewService,
        private TrainingVisibilityScope $trainingVisibilityScope,
        private TrainingCapabilityResolver $trainingCapabilityResolver,
        private BasePortalNavigationService $basePortalNavigationService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(User $user): array
    {
        $today = CarbonImmutable::today();
        $servingTrainings = $this->loadServingTrainings($user);
        $baseChurch = $this->resolveBaseChurch($user);
        $hostedEvents = $this->loadHostedEvents($user);
        $inventoryAlerts = $this->loadInventoryAlerts($user);

        $upcomingServing = $servingTrainings
            ->filter(fn (Training $training): bool => $this->firstEventDate($training)?->greaterThanOrEqualTo($today) ?? false)
            ->take(4)
            ->map(fn (Training $training): array => $this->mapTraining($user, $training, 'serving'))
            ->values()
            ->all();

        $inProgressServing = $servingTrainings
            ->filter(fn (Training $training): bool => $this->isInProgress($training, $today))
            ->take(3)
            ->map(fn (Training $training): array => $this->mapTraining($user, $training, 'serving', 'em andamento'))
            ->values()
            ->all();

        $hostedCatalog = $hostedEvents
            ->map(fn (Training $training): array => $this->mapTraining($user, $training, 'hosted'))
            ->values()
            ->all();

        $hostedUpcoming = $hostedEvents
            ->filter(fn (Training $training): bool => $this->firstEventDate($training)?->greaterThanOrEqualTo($today) ?? false)
            ->take(4)
            ->map(fn (Training $training): array => $this->mapTraining($user, $training, 'hosted'))
            ->values()
            ->all();

        $outsideBaseServing = $servingTrainings
            ->filter(fn (Training $training): bool => (int) $training->church_id !== 0 && (int) $training->church_id !== (int) $user->church_id)
            ->take(4)
            ->map(fn (Training $training): array => $this->mapTraining($user, $training, 'serving'))
            ->values()
            ->all();

        $pendingProgramming = $servingTrainings
            ->merge($hostedEvents)
            ->unique('id')
            ->filter(fn (Training $training): bool => $this->resolveScheduleRoute($user, $training) !== null)
            ->filter(fn (Training $training): bool => $this->hasScheduleIssue($training))
            ->sortBy(fn (Training $training): string => (string) $this->firstEventDate($training)?->format('Y-m-d'))
            ->take(5)
            ->map(fn (Training $training): array => $this->mapTraining($user, $training, 'programming', 'programacao pendente'))
            ->values();

        $pendingReports = $servingTrainings
            ->filter(fn (Training $training): bool => $this->resolveReportRoute($user, $training) !== null)
            ->filter(fn (Training $training): bool => $this->firstEventDate($training)?->isBefore($today) ?? false)
            ->filter(fn (Training $training): bool => blank(trim((string) $training->notes)))
            ->sortByDesc(fn (Training $training): string => (string) $this->firstEventDate($training)?->format('Y-m-d'))
            ->take(5)
            ->map(fn (Training $training): array => $this->mapTraining($user, $training, 'report', 'relatorio pendente'))
            ->values();

        $teacherDashboard = $user->can('access-teacher')
            ? $this->teacherDashboardService->build($user, DashboardPeriod::Quarter)
            : null;

        $directorDashboard = $user->can('access-director')
            ? $this->directorDashboardService->build(DashboardPeriod::Quarter)
            : null;

        $mentorDashboard = $user->can('access-mentor')
            ? $this->mentorTrainingOverviewService->dashboardData($user)
            : null;

        return [
            'counts' => [
                'serving_upcoming' => count($upcomingServing),
                'hosted_events' => count($hostedUpcoming),
                'hosted_events_total' => count($hostedCatalog),
                'serving_outside_base' => count($outsideBaseServing),
                'pending_programming' => $pendingProgramming->count(),
                'pending_reports' => $pendingReports->count(),
                'inventory_alerts' => $inventoryAlerts->count(),
                'in_progress_serving' => count($inProgressServing),
            ],
            'my_base' => [
                'church' => $baseChurch ? [
                    'id' => $baseChurch->id,
                    'name' => $baseChurch->name,
                    'city' => $baseChurch->city,
                    'state' => $baseChurch->state,
                    'host_label' => $baseChurch->hostChurch ? 'Base anfitria configurada' : 'Base sem configuracao de anfitria',
                ] : null,
                'hosted_events' => $hostedUpcoming,
                'inventory_alerts' => $inventoryAlerts->all(),
            ],
            'serving' => [
                'upcoming' => $upcomingServing,
                'in_progress' => $inProgressServing,
                'pending_reports' => $pendingReports->all(),
                'outside_base' => $outsideBaseServing,
            ],
            'base_events' => [
                'all' => $hostedCatalog,
                'upcoming' => $hostedUpcoming,
                'pending_programming' => $pendingProgramming->all(),
                'recent' => $hostedEvents
                    ->filter(fn (Training $training): bool => $this->firstEventDate($training)?->isBefore($today) ?? false)
                    ->sortByDesc(fn (Training $training): string => (string) $this->firstEventDate($training)?->format('Y-m-d'))
                    ->take(4)
                    ->map(fn (Training $training): array => $this->mapTraining($user, $training, 'hosted', 'concluido'))
                    ->values()
                    ->all(),
            ],
            'alerts' => [
                'pending_programming' => $pendingProgramming->all(),
                'pending_reports' => $pendingReports->all(),
                'inventory' => $inventoryAlerts->all(),
            ],
            'shortcuts' => $this->buildShortcuts($user, $baseChurch, $servingTrainings, $hostedEvents),
            'snapshots' => [
                'teacher' => $teacherDashboard ? [
                    'period_label' => $teacherDashboard['periodLabel'],
                    'future_trainings' => collect($teacherDashboard['kpis'])->firstWhere('key', 'future_trainings')['value'] ?? 0,
                    'schedule_pendencies' => collect($teacherDashboard['kpis'])->firstWhere('key', 'schedule_pendencies')['value'] ?? 0,
                    'paid_students' => collect($teacherDashboard['kpis'])->firstWhere('key', 'paid_students')['value'] ?? 0,
                ] : null,
                'director' => $directorDashboard ? [
                    'churches_reached' => collect($directorDashboard['kpis'])->firstWhere('key', 'churches_reached')['value'] ?? 0,
                    'new_churches' => collect($directorDashboard['kpis'])->firstWhere('key', 'new_churches')['value'] ?? 0,
                    'financial_bottlenecks' => count($directorDashboard['alerts']['financialBottlenecks'] ?? []),
                ] : null,
                'mentor' => $mentorDashboard ? [
                    'trainings_count' => $mentorDashboard['trainings_count'],
                    'teams_count' => $mentorDashboard['teams_count'],
                    'completed_sessions_count' => $mentorDashboard['completed_sessions_count'],
                ] : null,
            ],
        ];
    }

    /**
     * @return Collection<int, Training>
     */
    protected function loadServingTrainings(User $user): Collection
    {
        return Training::query()
            ->with([
                'course:id,name,type',
                'church:id,name,city,state',
                'teacher:id,name',
                'assistantTeachers:id,name',
                'mentors:id,name',
                'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
                'scheduleItems' => fn ($query) => $query->orderBy('date')->orderBy('starts_at')->orderBy('position'),
            ])
            ->withCount(['students', 'mentors'])
            ->tap(fn (Builder $query) => $this->trainingVisibilityScope->apply($query, $user))
            ->orderBy('id')
            ->get()
            ->sortBy(fn (Training $training): string => (string) $this->firstEventDate($training)?->format('Y-m-d'))
            ->values();
    }

    /**
     * @return Collection<int, Training>
     */
    protected function loadHostedEvents(User $user): Collection
    {
        if (! $user->church_id || ! $this->basePortalNavigationService->canViewBaseEvents($user)) {
            return collect();
        }

        return Training::query()
            ->with([
                'course:id,name,type',
                'church:id,name,city,state',
                'teacher:id,name',
                'assistantTeachers:id,name',
                'mentors:id,name',
                'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
                'scheduleItems' => fn ($query) => $query->orderBy('date')->orderBy('starts_at')->orderBy('position'),
            ])
            ->withCount(['students', 'mentors'])
            ->where('church_id', $user->church_id)
            ->get()
            ->sortBy(fn (Training $training): string => (string) $this->firstEventDate($training)?->format('Y-m-d'))
            ->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function loadInventoryAlerts(User $user): Collection
    {
        $inventoryIds = Inventory::query()
            ->when(
                $this->basePortalNavigationService->canViewBaseInventory($user),
                fn (Builder $query) => $query->where('kind', 'base')->where('church_id', $user->church_id),
                fn (Builder $query) => $query->when($user->can('access-director'), fn (Builder $directorQuery) => $directorQuery, fn (Builder $ownedQuery) => $ownedQuery->where('user_id', $user->id))
            )
            ->pluck('id');

        if ($inventoryIds->isEmpty()) {
            return collect();
        }

        return Inventory::query()
            ->with(['responsibleUser:id,name', 'church:id,name'])
            ->whereIn('id', $inventoryIds->all())
            ->get()
            ->map(function (Inventory $inventory) use ($user): array {
                $lowStockCount = (int) DB::table('materials')
                    ->leftJoin('inventory_material', function ($join) use ($inventory): void {
                        $join
                            ->on('inventory_material.material_id', '=', 'materials.id')
                            ->where('inventory_material.inventory_id', '=', $inventory->id);
                    })
                    ->where('materials.minimum_stock', '>', 0)
                    ->whereRaw('COALESCE(inventory_material.current_quantity, 0) < materials.minimum_stock')
                    ->count();

                return [
                    'id' => $inventory->id,
                    'name' => $inventory->name,
                    'kind' => $inventory->kind ?: 'teacher',
                    'responsible' => $inventory->responsibleUser?->name,
                    'church' => $inventory->church?->name,
                    'low_stock_count' => $lowStockCount,
                    'is_active' => $inventory->is_active,
                    'route' => $this->resolveInventoryRoute($user, $inventory),
                ];
            })
            ->filter(fn (array $inventory): bool => $inventory['low_stock_count'] > 0 || ! $inventory['is_active'])
            ->take(5)
            ->values();
    }

    protected function resolveBaseChurch(User $user): ?\App\Models\Church
    {
        if (! $this->basePortalNavigationService->canViewMyBase($user)) {
            return null;
        }

        if (! $user->relationLoaded('church')) {
            $user->load('church.hostChurch');
        }

        return $user->church;
    }

    /**
     * @param  'serving'|'hosted'|'programming'|'report'  $context
     * @return array<string, mixed>
     */
    protected function mapTraining(User $user, Training $training, string $context, ?string $status = null): array
    {
        $firstDate = $this->firstEventDate($training);

        return [
            'id' => $training->id,
            'title' => $this->trainingLabel($training),
            'church_name' => $training->church?->name ?? 'Base nao informada',
            'schedule_summary' => $this->scheduleSummary($training),
            'location' => $this->locationLabel($training),
            'students_count' => (int) ($training->students_count ?? 0),
            'mentors_count' => (int) ($training->mentors_count ?? 0),
            'first_date' => $firstDate?->format('d/m/Y'),
            'payment_confirmed' => false,
            'receipt_in_review' => false,
            'receipt_pending' => false,
            'payment_required' => true,
            'accredited' => false,
            'kit' => false,
            'status' => $status ?? $this->statusLabel($training),
            'detail_route' => $this->resolveTrainingRoute($user, $training, $context),
            'report_route' => $this->resolveReportRoute($user, $training),
            'schedule_route' => $this->resolveScheduleRoute($user, $training),
            'is_hosted_by_base' => (int) $user->church_id !== 0 && (int) $user->church_id === (int) $training->church_id,
            'is_serving_context' => $this->trainingCapabilityResolver->canViewAsServingContext($user, $training),
            'context_badges' => $this->contextBadges($user, $training),
        ];
    }

    protected function resolveTrainingRoute(User $user, Training $training, string $context): string
    {
        if ($this->trainingCapabilityResolver->canViewAsBaseContext($user, $training)) {
            return route('app.portal.base.trainings.show', $training);
        }

        if ($user->can('access-director') && $this->trainingCapabilityResolver->canView($user, $training)) {
            return route('app.director.trainings.show', $training);
        }

        if ($user->can('access-teacher') && $this->trainingCapabilityResolver->canViewAsTeacherContext($user, $training)) {
            return route('app.teacher.trainings.show', $training);
        }

        if ($user->can('access-mentor') && $context !== 'hosted') {
            return route('app.mentor.trainings.show', $training);
        }

        return route('app.portal.base.dashboard');
    }

    protected function resolveReportRoute(User $user, Training $training): ?string
    {
        if ($user->can('access-director') && $this->trainingCapabilityResolver->canEdit($user, $training)) {
            return route('app.director.training.testimony', $training);
        }

        if ($user->can('access-teacher') && $this->trainingCapabilityResolver->canEditAsTeacherContext($user, $training)) {
            return route('app.teacher.trainings.testimony', $training);
        }

        return null;
    }

    protected function resolveScheduleRoute(User $user, Training $training): ?string
    {
        if ($this->trainingCapabilityResolver->canViewAsBaseContext($user, $training)) {
            return route('app.portal.base.trainings.schedule', $training);
        }

        if ($user->can('access-director') && $this->trainingCapabilityResolver->canManageSchedule($user, $training)) {
            return route('app.director.trainings.schedule', $training);
        }

        if ($user->can('access-teacher') && $this->trainingCapabilityResolver->canManageSchedule($user, $training)) {
            return route('app.teacher.trainings.schedule', $training);
        }

        return null;
    }

    protected function resolveInventoryRoute(User $user, Inventory $inventory): string
    {
        if ($inventory->isBaseInventory() && $this->basePortalNavigationService->canViewBaseInventory($user) && (int) $inventory->church_id === (int) $user->church_id) {
            return route('app.portal.base.inventory');
        }

        if ($user->can('access-director')) {
            return route('app.director.inventory.show', $inventory);
        }

        if ($user->can('access-teacher') && (int) $inventory->user_id === (int) $user->id) {
            return route('app.teacher.inventory.show', $inventory);
        }

        return route('app.portal.base.my-base');
    }

    /**
     * @return array<int, array<string, string>>
     */
    protected function buildShortcuts(User $user, ?\App\Models\Church $baseChurch, Collection $servingTrainings, Collection $hostedEvents): array
    {
        $navigation = $this->basePortalNavigationService->summary($user);
        $shortcuts = [];

        if ($navigation['canViewMyBase']) {
            $shortcuts[] = [
                'label' => 'Minha Base',
                'description' => 'Abrir base local, igreja anfitria e acervo relacionado.',
                'route' => route('app.portal.base.my-base'),
            ];
        }

        if ($navigation['canViewBaseInventory']) {
            $shortcuts[] = [
                'label' => 'Acervo da base',
                'description' => 'Ver saldos locais, entradas, uso por evento e reposicoes.',
                'route' => route('app.portal.base.inventory'),
            ];
        }

        if ($navigation['canViewServing']) {
            $shortcuts[] = [
                'label' => 'Treinamentos em que sirvo',
                'description' => 'Ver agenda operacional, programacao e relatorios.',
                'route' => route('app.portal.base.serving'),
            ];
        }

        if ($navigation['canViewBaseEvents']) {
            $shortcuts[] = [
                'label' => 'Eventos da Base',
                'description' => 'Acompanhar o que esta sendo sediado pela sua base.',
                'route' => route('app.portal.base.events'),
            ];
        }

        if ($user->can('access-teacher') && $navigation['canViewServing']) {
            $shortcuts[] = [
                'label' => 'Treinamentos legados',
                'description' => 'Entrar no modulo operacional atual do professor.',
                'route' => route('app.teacher.trainings.index'),
            ];
        }

        if ($user->can('manageChurches') && $baseChurch && $navigation['canViewMyBase']) {
            $shortcuts[] = [
                'label' => 'Gerir minha base',
                'description' => 'Abrir o cadastro e operacao da igreja-base.',
                'route' => $user->can('access-director')
                    ? route('app.director.church.show', $baseChurch)
                    : route('app.teacher.churches.show', $baseChurch),
            ];
        }

        if ($user->can('access-mentor') && $servingTrainings->isNotEmpty() && $navigation['canViewServing']) {
            $shortcuts[] = [
                'label' => 'Mentoria e OJT',
                'description' => 'Continuar a trilha de acompanhamento e sessoes.',
                'route' => route('app.mentor.ojt.sessions.index'),
            ];
        }

        if ($navigation['canViewBaseEvents'] && $hostedEvents->isNotEmpty()) {
            $shortcuts[] = [
                'label' => 'Proximo evento sediado',
                'description' => 'Abrir rapidamente o proximo evento da base.',
                'route' => $this->resolveTrainingRoute($user, $hostedEvents->first(), 'hosted'),
            ];
        }

        return $shortcuts;
    }

    protected function trainingLabel(Training $training): string
    {
        return trim((string) (($training->course?->type ? $training->course->type.' - ' : '').($training->course?->name ?? 'Treinamento')));
    }

    protected function hasScheduleIssue(Training $training): bool
    {
        if ($training->eventDates->isEmpty()) {
            return true;
        }

        return ! DayScheduleHelper::hasAllDaysMatch($training->eventDates, $training->scheduleItems);
    }

    protected function firstEventDate(Training $training): ?CarbonImmutable
    {
        $date = $training->eventDates->first()?->date;

        return $date ? CarbonImmutable::parse((string) $date) : null;
    }

    protected function isInProgress(Training $training, CarbonImmutable $today): bool
    {
        $firstDate = $this->firstEventDate($training);
        $lastDate = $training->eventDates->last()?->date;

        if (! $firstDate || ! $lastDate) {
            return false;
        }

        return $firstDate->startOfDay()->lte($today) && CarbonImmutable::parse((string) $lastDate)->endOfDay()->gte($today);
    }

    protected function statusLabel(Training $training): string
    {
        $firstDate = $this->firstEventDate($training);

        if (! $firstDate) {
            return 'a confirmar';
        }

        if ($firstDate->isFuture()) {
            return 'proximo';
        }

        return 'operacional';
    }

    protected function scheduleSummary(Training $training): string
    {
        if ($training->eventDates->isEmpty()) {
            return 'Datas a confirmar';
        }

        $first = $training->eventDates->first()?->date;
        $last = $training->eventDates->last()?->date;

        if ($first === $last) {
            return CarbonImmutable::parse((string) $first)->translatedFormat('d/m/Y');
        }

        return CarbonImmutable::parse((string) $first)->format('d/m').' a '.CarbonImmutable::parse((string) $last)->format('d/m/Y');
    }

    protected function locationLabel(Training $training): string
    {
        $parts = array_filter([
            $training->church?->name,
            $training->church?->city,
            $training->church?->state,
        ]);

        return $parts !== [] ? implode(' - ', $parts) : 'Base a confirmar';
    }

    /**
     * @return array<int, string>
     */
    protected function contextBadges(User $user, Training $training): array
    {
        $isHostedByBase = (int) $user->church_id !== 0 && (int) $user->church_id === (int) $training->church_id;
        $servesTraining = $this->trainingCapabilityResolver->canViewAsServingContext($user, $training);

        return match (true) {
            $isHostedByBase && $servesTraining => ['Evento da minha base', 'Tambem sirvo aqui'],
            $isHostedByBase => ['Evento da minha base'],
            $servesTraining => ['Sirvo fora da minha base'],
            default => [],
        };
    }
}
