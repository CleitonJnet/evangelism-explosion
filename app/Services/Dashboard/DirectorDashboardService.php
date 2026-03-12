<?php

namespace App\Services\Dashboard;

use App\Helpers\DayScheduleHelper;
use App\Helpers\NameUser;
use App\Models\Course;
use App\Models\StpApproach;
use App\Models\Training;
use App\Models\TrainingNewChurch;
use App\Models\User;
use App\Services\Metrics\TrainingDiscipleshipMetricsService;
use App\Services\Metrics\TrainingFinanceMetricsService;
use App\Services\Metrics\TrainingRegistrationMetricsService;
use App\Services\Metrics\TrainingStpMetricsService;
use App\Support\Dashboard\Builders\ChartPayloadBuilder;
use App\Support\Dashboard\Data\ChartDatasetData;
use App\Support\Dashboard\Data\TimeSeriesPointData;
use App\Support\Dashboard\Enums\DashboardPeriod;
use App\TrainingStatus;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class DirectorDashboardService
{
    public function __construct(
        private ChartPayloadBuilder $chartPayloadBuilder,
        private TrainingRegistrationMetricsService $registrationMetrics,
        private TrainingFinanceMetricsService $financeMetrics,
        private TrainingStpMetricsService $stpMetrics,
        private TrainingDiscipleshipMetricsService $discipleshipMetrics,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(
        DashboardPeriod $period,
        ?CarbonImmutable $startDate = null,
        ?CarbonImmutable $endDate = null,
    ): array {
        $hasCustomRange = $startDate !== null && $endDate !== null;
        $range = $hasCustomRange
            ? [
                'start' => $startDate->startOfDay(),
                'end' => $endDate->endOfDay(),
            ]
            : $period->range(CarbonImmutable::now());
        $trainings = $this->loadTrainings()
            ->filter(fn (Training $training): bool => $this->isTrainingWithinRange($training, $range['start'], $range['end']))
            ->values();
        $approachesByTraining = $this->loadApproachesByTraining($trainings);
        $newChurches = $this->loadNewChurches($trainings, $range['start'], $range['end']);
        $discipleship = $this->discipleshipMetrics->summarizeParallelTrack($approachesByTraining->flatten(1));
        $paymentSummary = $this->summarizePayments($trainings);

        return [
            'period' => $period,
            'periodLabel' => $hasCustomRange ? 'Período personalizado' : $period->label(),
            'rangeLabel' => sprintf(
                '%s a %s',
                $range['start']->translatedFormat('d/m/Y'),
                $range['end']->translatedFormat('d/m/Y'),
            ),
            'periodOptions' => DashboardPeriod::options(),
            'filters' => [
                'startDate' => $hasCustomRange ? $range['start']->toDateString() : null,
                'endDate' => $hasCustomRange ? $range['end']->toDateString() : null,
                'usingCustomRange' => $hasCustomRange,
            ],
            'eventStatusOverview' => $this->buildEventStatusOverview($trainings),
            'kpis' => $this->buildKpis($trainings, $newChurches, $discipleship, $paymentSummary),
            'charts' => $this->buildCharts($range['start'], $range['end'], $trainings, $newChurches, $discipleship, $paymentSummary),
            'leadershipTeachers' => $this->buildLeadershipTeachersTable(),
            'alerts' => $this->buildAlerts($trainings, $paymentSummary),
        ];
    }

    /**
     * @return Collection<int, Training>
     */
    private function loadTrainings(): Collection
    {
        return Training::query()
            ->with([
                'course.ministry',
                'church:id,name,city,state',
                'teacher:id,name',
                'assistantTeachers:id,name',
                'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
                'scheduleItems' => fn ($query) => $query->orderBy('date')->orderBy('starts_at')->orderBy('position'),
                'students' => fn ($query) => $query
                    ->select('users.id', 'users.name', 'users.church_id', 'users.church_temp_id', 'users.is_pastor')
                    ->with(['church:id,name,state,city', 'church_temp:id,name,status,state,city']),
                'stpSessions:id,training_id,sequence,label,starts_at,ends_at,status',
            ])
            ->withCount(['students', 'mentors'])
            ->get();
    }

    /**
     * @param  Collection<int, Training>  $trainings
     * @return Collection<int, Collection<int, StpApproach>>
     */
    private function loadApproachesByTraining(Collection $trainings): Collection
    {
        if ($trainings->isEmpty()) {
            return collect();
        }

        return StpApproach::query()
            ->with('session:id,training_id,starts_at,ends_at,status')
            ->whereIn('training_id', $trainings->pluck('id')->all())
            ->get()
            ->groupBy('training_id');
    }

    /**
     * @param  Collection<int, Training>  $trainings
     * @return Collection<int, TrainingNewChurch>
     */
    private function loadNewChurches(Collection $trainings, CarbonImmutable $start, CarbonImmutable $end): Collection
    {
        if ($trainings->isEmpty()) {
            return collect();
        }

        return TrainingNewChurch::query()
            ->with(['church:id,name,state,city', 'training:id'])
            ->whereIn('training_id', $trainings->pluck('id')->all())
            ->whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()])
            ->get();
    }

    private function isTrainingWithinRange(Training $training, CarbonImmutable $start, CarbonImmutable $end): bool
    {
        if ($training->eventDates->isNotEmpty()) {
            return $training->eventDates->contains(function ($eventDate) use ($start, $end): bool {
                if ($eventDate->date === null) {
                    return false;
                }

                $date = CarbonImmutable::parse((string) $eventDate->date);

                return $date->betweenIncluded($start, $end);
            });
        }

        return CarbonImmutable::parse($training->created_at)->betweenIncluded($start, $end);
    }

    /**
     * @param  Collection<int, Training>  $trainings
     * @param  Collection<int, TrainingNewChurch>  $newChurches
     * @param  array<string, int|string|float>  $discipleship
     * @param  array<string, mixed>  $paymentSummary
     * @return array<int, array{key: string, label: string, value: string|int|float, description: string}>
     */
    private function buildKpis(
        Collection $trainings,
        Collection $newChurches,
        array $discipleship,
        array $paymentSummary,
    ): array {
        $registrationTrainings = $this->registrationEligibleTrainings($trainings);
        $futureTrainings = $trainings->filter(
            fn (Training $training): bool => $this->firstEventDate($training)?->greaterThanOrEqualTo(CarbonImmutable::today()) ?? false,
        );
        $completedTrainings = $trainings->where('status', TrainingStatus::Completed);

        $churchesReached = $registrationTrainings
            ->flatMap(fn (Training $training) => $training->students)
            ->pluck('church_id')
            ->filter()
            ->unique()
            ->count();

        $activeTeachers = $trainings
            ->flatMap(function (Training $training): array {
                $ids = [(int) $training->teacher_id];

                foreach ($training->assistantTeachers as $assistantTeacher) {
                    $ids[] = (int) $assistantTeacher->id;
                }

                return $ids;
            })
            ->filter()
            ->unique()
            ->count();

        $pastorsTrained = $registrationTrainings
            ->flatMap(fn (Training $training) => $training->students)
            ->filter(fn (User $student): bool => (bool) $student->is_pastor)
            ->count();

        $stpSummary = $this->summarizeStp($trainings);

        return [
            $this->kpi('trainings', 'Treinamentos no período', $trainings->count(), 'Janela nacional consolidada'),
            // $this->kpi('completed_trainings', 'Treinamentos concluídos', $completedTrainings->count(), 'Eventos encerrados no período'),
            $this->kpi('future_trainings', 'Treinamentos futuros', $futureTrainings->count(), 'Pipeline imediato do ministério'),
            $this->kpi('churches_reached', 'Igrejas alcançadas', $churchesReached, 'Igrejas com inscritos no período'),
            $this->kpi('new_churches', 'Novas igrejas', $newChurches->pluck('church_id')->filter()->unique()->count(), 'Expansão registrada via treinamento'),
            $this->kpi('active_teachers', 'Professores ativos', $activeTeachers, 'Titulares e auxiliares em atuação'),
            $this->kpi('registrations', 'Inscritos', $registrationTrainings->sum('students_count'), 'Volume nacional de participantes em treinamentos agendados e concluídos'),
            $this->kpi('paid_students', 'Pagantes', $paymentSummary['paid_students'], 'Pagamentos confirmados'),
            $this->kpi('payment_rate', 'Taxa de pagamento', $paymentSummary['payment_rate_label'], 'Conversão financeira dos inscritos'),
            $this->kpi('pastors_trained', 'Pastores treinados', $pastorsTrained, 'Liderança pastoral alcançada'),
            $this->kpi('gospel_explained', 'Evangelho explicado', $stpSummary['evangelho_explicado'], 'Total consolidado de STP'),
            $this->kpi('people_reached', 'Pessoas alcançadas', $stpSummary['pessoas_ouviram'], 'Pessoas que ouviram o evangelho'),
            $this->kpi('decisions', 'Decisões', $stpSummary['decisao'], 'Respostas registradas'),
            $this->kpi('scheduled_visits', 'Visitas agendadas', $stpSummary['visita_agendada'], 'Follow-ups com data marcada'),
            $this->kpi('discipleship', 'Acompanhamentos discipuladores', $discipleship['people_in_follow_up'], 'Trilha paralela ativa'),
            $this->kpi('ee_balance', 'Saldo EE', $paymentSummary['ee_balance_label'], 'Soma nacional da parcela EE'),
            $this->kpi('host_balance', 'Saldo igreja anfitriã', $paymentSummary['host_balance_label'], 'Soma nacional da parcela anfitriã'),
        ];
    }

    /**
     * @param  Collection<int, Training>  $trainings
     * @return array<string, int>
     */
    private function summarizeStp(Collection $trainings): array
    {
        $summary = [
            'evangelho_explicado' => 0,
            'pessoas_ouviram' => 0,
            'decisao' => 0,
            'visita_agendada' => 0,
        ];

        foreach ($trainings as $training) {
            $stpSummary = $this->stpMetrics->buildTrainingSummary($training);
            $summary['evangelho_explicado'] += (int) $stpSummary['evangelho_explicado'];
            $summary['pessoas_ouviram'] += (int) $stpSummary['pessoas_ouviram'];
            $summary['decisao'] += (int) $stpSummary['decisao'];
            $summary['visita_agendada'] += (int) $stpSummary['visita_agendada'];
        }

        return $summary;
    }

    /**
     * @param  Collection<int, Training>  $trainings
     * @return array<string, mixed>
     */
    private function summarizePayments(Collection $trainings): array
    {
        $registrationTrainings = $this->registrationEligibleTrainings($trainings);
        $paidStudents = 0;
        $registrations = (int) $registrationTrainings->sum('students_count');
        $eeBalance = 0.0;
        $hostBalance = 0.0;
        $underperformingTrainings = [];

        foreach ($registrationTrainings as $training) {
            $finance = $this->financeMetrics->build($training);
            $paidStudents += (int) $finance['paidStudentsCount'];
            $eeBalance += $this->moneyToFloat($finance['eeMinistryBalance']);
            $hostBalance += $this->moneyToFloat($finance['hostChurchExpenseBalance']);

            $trainingRate = $training->students_count > 0
                ? ((int) $finance['paidStudentsCount'] / (int) $training->students_count)
                : 1;

            if ($training->students_count > 0 && $trainingRate < 0.5) {
                $underperformingTrainings[] = [
                    'training' => $training,
                    'label' => $this->trainingLabel($training),
                    'payment_rate' => (int) round($trainingRate * 100),
                ];
            }
        }

        $paymentRate = $registrations > 0 ? ($paidStudents / $registrations) : 0.0;

        return [
            'paid_students' => $paidStudents,
            'payment_rate' => $paymentRate,
            'payment_rate_label' => sprintf('%d%%', (int) round($paymentRate * 100)),
            'ee_balance' => $eeBalance,
            'host_balance' => $hostBalance,
            'ee_balance_label' => $this->formatMoney($eeBalance),
            'host_balance_label' => $this->formatMoney($hostBalance),
            'financial_bottlenecks' => collect($underperformingTrainings)
                ->sortBy('payment_rate')
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  Collection<int, Training>  $trainings
     * @param  Collection<int, TrainingNewChurch>  $newChurches
     * @param  array<string, mixed>  $discipleship
     * @param  array<string, mixed>  $paymentSummary
     * @return array<int, array<string, mixed>>
     */
    private function buildCharts(
        CarbonImmutable $rangeStart,
        CarbonImmutable $rangeEnd,
        Collection $trainings,
        Collection $newChurches,
        array $discipleship,
        array $paymentSummary,
    ): array {
        $registrationTrainings = $this->registrationEligibleTrainings($trainings);
        $series = [];
        $decisionsSeries = [];
        $newChurchesSeries = [];

        $months = $rangeStart->startOfMonth()->diffInMonths($rangeEnd->startOfMonth()) + 1;

        for ($index = 0; $index < $months; $index++) {
            $month = $rangeStart->addMonths($index);

            $monthTrainings = $trainings->filter(
                fn (Training $training): bool => $this->firstEventDate($training)?->isSameMonth($month)
                    ?? CarbonImmutable::parse($training->created_at)->isSameMonth($month),
            );

            $series[] = new TimeSeriesPointData($month->toDateString(), $monthTrainings->count());
            $decisionsSeries[] = new TimeSeriesPointData(
                $month->toDateString(),
                $monthTrainings->sum(fn (Training $training): int => (int) $this->stpMetrics->buildTrainingSummary($training)['decisao']),
            );
            $newChurchesSeries[] = new TimeSeriesPointData(
                $month->toDateString(),
                $newChurches->filter(fn (TrainingNewChurch $item): bool => CarbonImmutable::parse($item->created_at)->isSameMonth($month))->count(),
            );
        }

        $coursesDistribution = $trainings
            ->groupBy(fn (Training $training): string => (string) ($training->course_id ?? 0))
            ->map(function (Collection $items, string $courseId): array {
                /** @var Training|null $training */
                $training = $items->first();

                return [
                    'id' => $courseId,
                    'label' => $training?->course?->name ?? 'Curso não informado',
                    'value' => $items->count(),
                    'color' => $training?->course ? $this->courseColor($training->course) : '#64748b',
                ];
            })
            ->sortByDesc('value')
            ->take(8);

        $registrationDatasets = $registrationTrainings
            ->groupBy(fn (Training $training): string => (string) ($training->course_id ?? 0))
            ->map(function (Collection $items) use ($rangeStart, $months): array {
                /** @var Training|null $training */
                $training = $items->first();

                $points = [];

                for ($index = 0; $index < $months; $index++) {
                    $month = $rangeStart->addMonths($index);
                    $monthRegistrations = $items
                        ->filter(function (Training $training) use ($month): bool {
                            $referenceDate = $this->firstEventDate($training) ?? CarbonImmutable::parse($training->created_at);

                            return $referenceDate->isSameMonth($month);
                        })
                        ->sum('students_count');

                    $points[] = new TimeSeriesPointData($month->toDateString(), (int) $monthRegistrations);
                }

                return [
                    'label' => $training?->course?->name ?? 'Curso não informado',
                    'value' => (int) $items->sum('students_count'),
                    'color' => $training?->course ? $this->courseColor($training->course) : '#64748b',
                    'points' => $points,
                ];
            })
            ->sortByDesc('value')
            ->values()
            ->map(fn (array $dataset): ChartDatasetData => new ChartDatasetData(
                label: $dataset['label'],
                data: array_map(
                    static fn (TimeSeriesPointData $point): array => $point->toArray(),
                    $dataset['points'],
                ),
                backgroundColor: $this->hexToRgba($dataset['color'], 0.12),
                borderColor: $dataset['color'],
                fill: false,
            ))
            ->all();

        $statesDistribution = $trainings
            ->groupBy(fn (Training $training): string => $training->state ?: ($training->church?->state ?: 'Sem UF'))
            ->map(fn (Collection $items): int => $items->count())
            ->sortDesc()
            ->take(8);

        $stateLabels = $statesDistribution->keys()->values();

        $coursesByState = $trainings
            ->filter(fn (Training $training): bool => $stateLabels->contains($training->state ?: ($training->church?->state ?: 'Sem UF')))
            ->groupBy(fn (Training $training): string => (string) ($training->course_id ?? 0))
            ->map(function (Collection $items) use ($stateLabels): array {
                /** @var Training|null $firstTraining */
                $firstTraining = $items->first();

                return [
                    'label' => $firstTraining?->course?->name ?? 'Curso não informado',
                    'color' => $firstTraining?->course ? $this->courseColor($firstTraining->course) : '#64748b',
                    'data' => $stateLabels
                        ->map(fn (string $state): int => $items->filter(
                            fn (Training $training): bool => ($training->state ?: ($training->church?->state ?: 'Sem UF')) === $state
                        )->count())
                        ->all(),
                ];
            })
            ->sortByDesc(fn (array $course): int => array_sum($course['data']))
            ->take(8)
            ->values();

        $teacherRanking = $trainings
            ->flatMap(function (Training $training): array {
                $rows = [];

                if ($training->teacher) {
                    $rows[] = $training->teacher->name;
                }

                foreach ($training->assistantTeachers as $assistantTeacher) {
                    $rows[] = $assistantTeacher->name.' (aux)';
                }

                return $rows;
            })
            ->countBy()
            ->sortDesc()
            ->take(8);

        $churchRanking = $registrationTrainings
            ->flatMap(fn (Training $training) => $training->students)
            ->map(fn (User $student): string => $this->registrationMetrics->resolveChurchLabel($student))
            ->countBy()
            ->sortDesc()
            ->take(8);

        $statusColors = [
            TrainingStatus::Scheduled->value => '#0f766e',
            TrainingStatus::Planning->value => '#d97706',
            TrainingStatus::Completed->value => '#2563eb',
            TrainingStatus::Canceled->value => '#dc2626',
        ];

        $statusSeries = collect(TrainingStatus::cases())
            ->map(function (TrainingStatus $status) use ($rangeStart, $months, $trainings, $statusColors): ChartDatasetData {
                $points = [];

                for ($index = 0; $index < $months; $index++) {
                    $month = $rangeStart->addMonths($index);
                    $monthTrainings = $trainings->filter(function (Training $training) use ($month, $status): bool {
                        $referenceDate = $this->firstEventDate($training) ?? CarbonImmutable::parse($training->created_at);

                        return $referenceDate->isSameMonth($month) && $training->status === $status;
                    });

                    $points[] = new TimeSeriesPointData($month->toDateString(), $monthTrainings->count());
                }

                return new ChartDatasetData(
                    label: $status->label(),
                    data: array_map(
                        static fn (TimeSeriesPointData $point): array => $point->toArray(),
                        $points,
                    ),
                    backgroundColor: $this->hexToRgba($statusColors[$status->value], 0.16),
                    borderColor: $statusColors[$status->value],
                    fill: false,
                    hidden: $status === TrainingStatus::Planning,
                );
            })
            ->all();

        return [
            $this->chartPayloadBuilder->timeSeries(
                id: 'director-trainings-month',
                title: 'Ritmo de eventos',
                datasets: [
                    ...$statusSeries,
                ],
                options: [
                    'xAxis' => ['unit' => $months <= 3 ? 'week' : 'month'],
                    'valueSuffix' => ' eventos',
                    'legendPosition' => 'bottom',
                ],
            )->toArray(),
            $this->chartPayloadBuilder->timeSeries(
                id: 'director-registrations-month',
                title: 'Inscritos por mês',
                datasets: $registrationDatasets,
                options: [
                    'xAxis' => ['unit' => $months <= 3 ? 'week' : 'month'],
                    'valueSuffix' => ' inscritos',
                    'legendPosition' => 'bottom',
                ],
            )->toArray(),
            $this->timeSeriesChart('director-decisions-month', 'Decisões por mês', 'Decisões', $decisionsSeries, ' decisões'),
            $this->timeSeriesChart('director-new-churches-month', 'Novas igrejas por mês', 'Novas igrejas', $newChurchesSeries, ' igrejas'),
            $this->chartPayloadBuilder->doughnut(
                id: 'director-distribution-course',
                title: 'Distribuição por ministério/curso',
                labels: $coursesDistribution->pluck('label')->all(),
                datasets: [
                    new ChartDatasetData(
                        label: 'Treinamentos',
                        data: $coursesDistribution->pluck('value')->all(),
                        backgroundColor: $coursesDistribution
                            ->pluck('color')
                            ->map(fn (string $color): string => $this->hexToRgba($color, 0.84))
                            ->all(),
                        borderColor: $coursesDistribution->pluck('color')->all(),
                    ),
                ],
                options: [
                    'legendPosition' => 'bottom',
                    'valueSuffix' => ' treinamentos',
                ],
            )->toArray(),
            $this->chartPayloadBuilder->bar(
                id: 'director-distribution-state',
                title: 'Distribuição por estado',
                labels: $stateLabels->all(),
                datasets: $coursesByState
                    ->map(fn (array $course): ChartDatasetData => new ChartDatasetData(
                        label: $course['label'],
                        data: $course['data'],
                        backgroundColor: $this->hexToRgba($course['color'], 0.84),
                        borderColor: $course['color'],
                    ))
                    ->all(),
                options: [
                    'valueSuffix' => ' treinamentos',
                    'stacked' => true,
                    'legendPosition' => 'bottom',
                ],
            )->toArray(),
            $this->chartPayloadBuilder->bar(
                id: 'director-ranking-teachers',
                title: 'Ranking de professores',
                labels: $teacherRanking->keys()->all(),
                datasets: [
                    new ChartDatasetData(
                        label: 'Atuações',
                        data: $teacherRanking->values()->all(),
                        backgroundColor: 'rgba(8, 145, 178, 0.78)',
                        borderColor: 'rgb(14, 116, 144)',
                    ),
                ],
                options: ['valueSuffix' => ' atuações'],
            )->toArray(),
            $this->chartPayloadBuilder->bar(
                id: 'director-ranking-churches',
                title: 'Ranking de igrejas',
                labels: $churchRanking->keys()->all(),
                datasets: [
                    new ChartDatasetData(
                        label: 'Inscritos',
                        data: $churchRanking->values()->all(),
                        backgroundColor: 'rgba(124, 58, 237, 0.76)',
                        borderColor: 'rgb(109, 40, 217)',
                    ),
                ],
                options: ['valueSuffix' => ' inscritos'],
            )->toArray(),
            $this->chartPayloadBuilder->bar(
                id: 'director-funnel-ministry',
                title: 'Funil ministerial',
                labels: ['Inscritos', 'Pagantes', 'Evangelho explicado', 'Pessoas alcançadas', 'Decisões', 'Acompanhamentos'],
                datasets: [
                    new ChartDatasetData(
                        label: 'Funil',
                        data: [
                            (int) $registrationTrainings->sum('students_count'),
                            (int) $paymentSummary['paid_students'],
                            (int) $this->summarizeStp($trainings)['evangelho_explicado'],
                            (int) $this->summarizeStp($trainings)['pessoas_ouviram'],
                            (int) $this->summarizeStp($trainings)['decisao'],
                            (int) $discipleship['people_in_follow_up'],
                        ],
                        backgroundColor: [
                            'rgba(15, 23, 42, 0.84)',
                            'rgba(37, 99, 235, 0.84)',
                            'rgba(249, 115, 22, 0.84)',
                            'rgba(8, 145, 178, 0.84)',
                            'rgba(34, 197, 94, 0.84)',
                            'rgba(99, 102, 241, 0.84)',
                        ],
                        borderColor: [
                            'rgb(15, 23, 42)',
                            'rgb(29, 78, 216)',
                            'rgb(194, 65, 12)',
                            'rgb(14, 116, 144)',
                            'rgb(22, 163, 74)',
                            'rgb(79, 70, 229)',
                        ],
                    ),
                ],
                options: ['valueSuffix' => ' registros'],
            )->toArray(),
        ];
    }

    /**
     * @param  Collection<int, Training>  $trainings
     * @return Collection<int, Training>
     */
    private function registrationEligibleTrainings(Collection $trainings): Collection
    {
        return $trainings->filter(
            fn (Training $training): bool => in_array($training->status, [TrainingStatus::Scheduled, TrainingStatus::Completed], true),
        )->values();
    }

    /**
     * @return array<int, array{name: string, church_name: string, city: string, state: string, profile_photo_url: ?string, initials: string, is_active: bool, principal_trainings_count: int, assistant_trainings_count: int, profile_url: string, courses: array<int, array{id: int, name: string, type: string, initials: string, color: string, ministry_name: string, is_active: bool}>}>
     */
    private function buildLeadershipTeachersTable(): array
    {
        return Course::query()
            ->leadership()
            ->with([
                'ministry:id,name',
                'teachers' => fn ($query) => $query
                    ->with('church:id,name,city,state')
                    ->withCount(['ledTrainings', 'assistedTrainings'])
                    ->orderBy('name'),
            ])
            ->orderBy('name')
            ->get()
            ->flatMap(function (Course $course): Collection {
                return $course->teachers->map(function (User $teacher) use ($course): array {
                    return [
                        'teacher_id' => $teacher->id,
                        'name' => $teacher->name,
                        'church_name' => $teacher->church?->name ?? 'Sem igreja vinculada',
                        'city' => $teacher->church?->city ?? 'Cidade não informada',
                        'state' => $teacher->church?->state ?? 'UF não informada',
                        'profile_photo_url' => $teacher->profile_photo_url,
                        'initials' => $teacher->initials(),
                        'is_active' => ((int) ($teacher->pivot->status ?? 0)) === 1,
                        'principal_trainings_count' => (int) ($teacher->led_trainings_count ?? 0),
                        'assistant_trainings_count' => (int) ($teacher->assisted_trainings_count ?? 0),
                        'profile_url' => route('app.profile.show', $teacher),
                        'course_id' => $course->id,
                        'course_name' => $course->name,
                        'course_type' => trim((string) $course->type) !== '' ? (string) $course->type : 'Curso',
                        'course_initials' => $this->courseInitials($course),
                        'course_color' => $this->courseColor($course),
                        'ministry_name' => $course->ministry?->name ?? 'Ministério não informado',
                    ];
                });
            })
            ->groupBy('teacher_id')
            ->map(function (Collection $items): array {
                /** @var array{name: string, church_name: string, city: string, state: string, profile_photo_url: ?string, initials: string, is_active: bool, principal_trainings_count: int, assistant_trainings_count: int, profile_url: string, course_name: string, course_initials: string, course_color: string, ministry_name: string}|null $first */
                $first = $items->first();

                return [
                    'name' => $first['name'] ?? 'Professor não informado',
                    'church_name' => $first['church_name'] ?? 'Sem igreja vinculada',
                    'city' => $first['city'] ?? 'Cidade não informada',
                    'state' => $first['state'] ?? 'UF não informada',
                    'profile_photo_url' => $first['profile_photo_url'] ?? null,
                    'initials' => $first['initials'] ?? '--',
                    'is_active' => $items->contains(fn (array $item): bool => $item['is_active']),
                    'principal_trainings_count' => (int) ($first['principal_trainings_count'] ?? 0),
                    'assistant_trainings_count' => (int) ($first['assistant_trainings_count'] ?? 0),
                    'profile_url' => $first['profile_url'] ?? '#',
                    'courses' => $items
                        ->map(fn (array $item): array => [
                            'id' => $item['course_id'],
                            'name' => $item['course_name'],
                            'type' => $item['course_type'],
                            'initials' => $item['course_initials'],
                            'color' => $item['course_color'],
                            'ministry_name' => $item['ministry_name'],
                            'is_active' => $item['is_active'],
                        ])
                        ->sortBy('name')
                        ->values()
                        ->all(),
                ];
            })
            ->sortBy('name')
            ->values()
            ->all();
    }

    private function courseInitials(Course $course): string
    {
        $initials = trim((string) $course->initials);

        if ($initials !== '') {
            return mb_strtoupper($initials, 'UTF-8');
        }

        return NameUser::initials($course->name);
    }

    private function courseColor(Course $course): string
    {
        $color = trim((string) $course->color);

        return preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $color) === 1 ? $color : '#475569';
    }

    private function hexToRgba(string $hex, float $alpha): string
    {
        $normalized = ltrim($hex, '#');

        if (strlen($normalized) === 3) {
            $normalized = collect(str_split($normalized))
                ->map(fn (string $char): string => $char.$char)
                ->implode('');
        }

        if (strlen($normalized) !== 6) {
            return 'rgba(100, 116, 139, '.$alpha.')';
        }

        /** @var int $red */
        $red = hexdec(substr($normalized, 0, 2));
        /** @var int $green */
        $green = hexdec(substr($normalized, 2, 2));
        /** @var int $blue */
        $blue = hexdec(substr($normalized, 4, 2));

        return sprintf('rgba(%d, %d, %d, %.2f)', $red, $green, $blue, $alpha);
    }

    /**
     * @param  Collection<int, Training>  $trainings
     * @return array<int, array{key: string, label: string, value: int, tone: string}>
     */
    private function buildEventStatusOverview(Collection $trainings): array
    {
        return [
            [
                'key' => TrainingStatus::Scheduled->key(),
                'value' => $trainings->where('status', TrainingStatus::Scheduled)->count(),
                'tone' => 'border-teal-200 bg-teal-50 text-teal-800',
            ],
            [
                'key' => TrainingStatus::Planning->key(),
                'value' => $trainings->where('status', TrainingStatus::Planning)->count(),
                'tone' => 'border-amber-200 bg-amber-50 text-amber-800',
            ],
            [
                'key' => TrainingStatus::Completed->key(),
                'value' => $trainings->where('status', TrainingStatus::Completed)->count(),
                'tone' => 'border-blue-200 bg-blue-50 text-blue-800',
            ],
            [
                'key' => TrainingStatus::Canceled->key(),
                'value' => $trainings->where('status', TrainingStatus::Canceled)->count(),
                'tone' => 'border-red-200 bg-red-50 text-red-800',
            ],
        ];
    }

    /**
     * @param  Collection<int, Training>  $trainings
     * @param  array<string, mixed>  $paymentSummary
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function buildAlerts(Collection $trainings, array $paymentSummary): array
    {
        $regions = $trainings
            ->groupBy(fn (Training $training): string => $training->state ?: ($training->church?->state ?: 'Sem UF'))
            ->map(fn (Collection $items, string $state): array => [
                'label' => $state,
                'value' => $items->count(),
                'context' => $items->count() <= 1 ? 'Cobertura baixa no período' : 'Cobertura ativa',
            ])
            ->filter(fn (array $item): bool => $item['value'] <= 1)
            ->values()
            ->all();

        $operationalRisks = $trainings
            ->map(function (Training $training): array {
                $pendingChurchIssues = $training->students
                    ->filter(fn (User $student): bool => $this->registrationMetrics->hasChurchIssue($student))
                    ->count();

                return [
                    'label' => $this->trainingLabel($training),
                    'context' => sprintf(
                        '%s%s',
                        $this->hasScheduleIssue($training) ? 'Programação pendente' : 'Inscrições pendentes',
                        $pendingChurchIssues > 0 ? sprintf(' | %d validações de igreja', $pendingChurchIssues) : '',
                    ),
                    'teacher_name' => $training->teacher?->name ?? 'Professor titular não informado',
                    'event_date' => $this->firstEventDate($training)?->format('d/m/Y') ?? 'Data não informada',
                    'route' => route('app.director.training.show', $training),
                ];
            })
            ->filter(fn (array $item): bool => $item['context'] !== 'Inscrições pendentes')
            ->take(6)
            ->values()
            ->all();

        $overloadedTeachers = $trainings
            ->groupBy('teacher_id')
            ->map(function (Collection $items): array {
                /** @var Training|null $firstTraining */
                $firstTraining = $items->first();

                return [
                    'label' => $firstTraining?->teacher?->name ?? 'Professor não informado',
                    'value' => $items->count(),
                    'context' => 'Treinamentos como titular no período',
                ];
            })
            ->filter(fn (array $item): bool => $item['value'] >= 3)
            ->sortByDesc('value')
            ->values()
            ->all();

        $lowRecurrenceCourses = $trainings
            ->groupBy('course_id')
            ->map(function (Collection $items): array {
                /** @var Training|null $training */
                $training = $items->first();

                return [
                    'label' => $training?->course?->name ?? 'Curso não informado',
                    'value' => $items->count(),
                    'context' => $training?->course?->ministry?->name ?? 'Sem ministério',
                ];
            })
            ->filter(fn (array $item): bool => $item['value'] <= 1)
            ->values()
            ->all();

        return [
            'regions' => $regions,
            'operationalRisks' => $operationalRisks,
            'financialBottlenecks' => $paymentSummary['financial_bottlenecks'],
            'overloadedTeachers' => $overloadedTeachers,
            'lowRecurrenceCourses' => $lowRecurrenceCourses,
        ];
    }

    /**
     * @param  array<int, TimeSeriesPointData>  $series
     */
    private function timeSeriesChart(
        string $id,
        string $title,
        string $label,
        array $series,
        string $suffix,
    ): array {
        return $this->chartPayloadBuilder->timeSeries(
            id: $id,
            title: $title,
            datasets: [
                new ChartDatasetData(
                    label: $label,
                    data: array_map(
                        static fn (TimeSeriesPointData $point): array => $point->toArray(),
                        $series,
                    ),
                    backgroundColor: 'rgba(14, 116, 144, 0.16)',
                    borderColor: 'rgb(14, 116, 144)',
                    fill: true,
                ),
            ],
            options: [
                'xAxis' => ['unit' => count($series) <= 3 ? 'week' : 'month'],
                'valueSuffix' => $suffix,
            ],
        )->toArray();
    }

    /**
     * @return array{key: string, label: string, value: string|int|float, description: string}
     */
    private function kpi(string $key, string $label, string|int|float $value, string $description): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'value' => $value,
            'description' => $description,
        ];
    }

    private function firstEventDate(Training $training): ?CarbonImmutable
    {
        $date = $training->eventDates->first()?->date;

        return $date ? CarbonImmutable::parse((string) $date) : null;
    }

    private function hasScheduleIssue(Training $training): bool
    {
        if ($training->eventDates->isEmpty()) {
            return true;
        }

        return ! DayScheduleHelper::hasAllDaysMatch($training->eventDates, $training->scheduleItems);
    }

    private function trainingLabel(Training $training): string
    {
        return trim((string) (($training->course?->type ? $training->course->type.' - ' : '').($training->course?->name ?? 'Treinamento')));
    }

    private function moneyToFloat(?string $formattedValue): float
    {
        if ($formattedValue === null) {
            return 0.0;
        }

        $normalized = str_replace(['R$', '.', ' '], '', $formattedValue);
        $normalized = str_replace(',', '.', $normalized);

        return (float) $normalized;
    }

    private function formatMoney(float $value): string
    {
        return 'R$ '.number_format($value, 2, ',', '.');
    }
}
