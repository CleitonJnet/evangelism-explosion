<?php

namespace App\Services\Dashboard;

use App\Helpers\DayScheduleHelper;
use App\Models\StpApproach;
use App\Models\Training;
use App\Models\User;
use App\Services\Metrics\TrainingDiscipleshipMetricsService;
use App\Services\Metrics\TrainingFinanceMetricsService;
use App\Services\Metrics\TrainingRegistrationMetricsService;
use App\Services\Metrics\TrainingStpMetricsService;
use App\Support\Dashboard\Builders\ChartPayloadBuilder;
use App\Support\Dashboard\Data\ChartDatasetData;
use App\Support\Dashboard\Data\TimeSeriesPointData;
use App\Support\Dashboard\Enums\DashboardPeriod;
use App\Support\TrainingAccess\TrainingVisibilityScope;
use App\TrainingStatus;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class TeacherDashboardService
{
    public function __construct(
        private TrainingVisibilityScope $visibilityScope,
        private ChartPayloadBuilder $chartPayloadBuilder,
        private TrainingRegistrationMetricsService $registrationMetrics,
        private TrainingFinanceMetricsService $financeMetrics,
        private TrainingStpMetricsService $stpMetrics,
        private TrainingDiscipleshipMetricsService $discipleshipMetrics,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(User $teacher, DashboardPeriod $period): array
    {
        $range = $period->range(CarbonImmutable::now());
        $today = CarbonImmutable::today();
        $visibleTrainings = $this->loadVisibleTrainings($teacher);
        $periodTrainings = $visibleTrainings
            ->filter(fn (Training $training): bool => $this->isTrainingWithinRange($training, $range['start'], $range['end']))
            ->values();

        $approachesByTraining = $this->loadApproachesByTraining($periodTrainings);

        $kpis = $this->buildKpis($periodTrainings, $approachesByTraining, $today);
        $evangelisticImpact = $this->buildEvangelisticImpact($periodTrainings);
        $discipleshipSummary = $this->buildDiscipleshipSummary($periodTrainings, $approachesByTraining);
        $operationalBlocks = $this->buildOperationalBlocks($periodTrainings, $approachesByTraining, $today);

        return [
            'period' => $period,
            'periodLabel' => $period->label(),
            'rangeLabel' => sprintf(
                '%s a %s',
                $range['start']->translatedFormat('d/m/Y'),
                $range['end']->translatedFormat('d/m/Y'),
            ),
            'periodOptions' => DashboardPeriod::options(),
            'kpis' => $kpis,
            'evangelisticImpact' => $evangelisticImpact,
            'discipleship' => $discipleshipSummary,
            'charts' => $this->buildCharts($period, $range['start'], $periodTrainings, $approachesByTraining, $discipleshipSummary),
            'operational' => $operationalBlocks,
            'quickActions' => $this->buildQuickActions($operationalBlocks['nextTrainings'][0]['training'] ?? null),
        ];
    }

    /**
     * @return Collection<int, Training>
     */
    private function loadVisibleTrainings(User $teacher): Collection
    {
        $query = Training::query()
            ->with([
                'course:id,name,type',
                'church:id,name',
                'eventDates' => fn ($eventDatesQuery) => $eventDatesQuery
                    ->orderBy('date')
                    ->orderBy('start_time'),
                'scheduleItems' => fn ($scheduleItemsQuery) => $scheduleItemsQuery
                    ->orderBy('date')
                    ->orderBy('starts_at')
                    ->orderBy('position'),
                'students' => fn ($studentsQuery) => $studentsQuery
                    ->select('users.id', 'users.name', 'users.church_id', 'users.church_temp_id', 'users.is_pastor')
                    ->with(['church:id,name', 'church_temp:id,name,status']),
                'mentors:id,name',
                'assistantTeachers:id,name',
                'stpSessions:id,training_id,sequence,label,starts_at,ends_at,status',
            ])
            ->withCount(['students', 'mentors']);

        return $this->visibilityScope
            ->apply($query, $teacher)
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
            ->whereIn('training_id', $trainings->pluck('id'))
            ->get()
            ->groupBy('training_id');
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
     * @param  Collection<int, Collection<int, StpApproach>>  $approachesByTraining
     * @return array<int, array{key: string, label: string, value: int, description: string}>
     */
    private function buildKpis(Collection $trainings, Collection $approachesByTraining, CarbonImmutable $today): array
    {
        $futureTrainings = $trainings
            ->filter(fn (Training $training): bool => $this->firstEventDate($training)?->greaterThanOrEqualTo($today) ?? false);

        $completedTrainings = $trainings
            ->filter(fn (Training $training): bool => $training->status === TrainingStatus::Completed);

        $pendingProgramming = $trainings
            ->filter(fn (Training $training): bool => $this->hasScheduleIssue($training));

        $pendingValidation = $trainings
            ->sum(fn (Training $training): int => $training->students
                ->filter(fn (User $student): bool => $this->registrationMetrics->hasChurchIssue($student))
                ->count());

        $stpPlanned = 0;
        $stpCompleted = 0;
        $discipleshipSessionsPlanned = 0;
        $discipleshipSessionsCompleted = 0;

        foreach ($trainings as $training) {
            $stpSummary = $this->stpMetrics->buildTrainingSummary($training);
            $discipleshipSummary = $this->discipleshipMetrics->summarizeParallelTrack(
                $approachesByTraining->get($training->id, collect()),
            );

            $stpPlanned += (int) $stpSummary['sessoes_previstas'];
            $stpCompleted += (int) $stpSummary['sessoes_concluidas'];
            $discipleshipSessionsPlanned += (int) $discipleshipSummary['sessions_planned'];
            $discipleshipSessionsCompleted += (int) $discipleshipSummary['sessions_completed'];
        }

        return [
            $this->kpi('trainings_in_period', 'Treinamentos no período', $trainings->count(), 'Base filtrada pelo período selecionado'),
            $this->kpi('future_trainings', 'Treinamentos futuros', $futureTrainings->count(), 'Agenda operacional ainda à frente'),
            $this->kpi('completed_trainings', 'Treinamentos concluídos', $completedTrainings->count(), 'Concluídos dentro da janela'),
            $this->kpi('registrations', 'Inscritos', $trainings->sum('students_count'), 'Volume total de participantes'),
            $this->kpi('paid_students', 'Pagantes', $trainings->sum(fn (Training $training): int => $this->financeMetrics->build($training)['paidStudentsCount']), 'Inscrições com pagamento confirmado'),
            $this->kpi('schedule_pendencies', 'Pendências de programação', $pendingProgramming->count(), 'Treinamentos com agenda incompleta ou inconsistente'),
            $this->kpi('church_pendencies', 'Pendências de validação/igreja', $pendingValidation, 'Inscrições com igreja ausente ou em validação'),
            $this->kpi('stp_sessions_planned', 'Sessões STP previstas', $stpPlanned, 'Carga prática planejada'),
            $this->kpi('stp_sessions_completed', 'Sessões STP concluídas', $stpCompleted, 'Sessões com abordagens concluídas ou revisadas'),
            $this->kpi('discipleship_sessions_planned', 'Sessões de discipulado previstas', $discipleshipSessionsPlanned, 'Trilha paralela de acompanhamento'),
            $this->kpi('discipleship_sessions_completed', 'Sessões de discipulado concluídas', $discipleshipSessionsCompleted, 'Sessões efetivamente finalizadas'),
        ];
    }

    /**
     * @param  Collection<int, Training>  $trainings
     * @return array<int, array{key: string, label: string, value: int}>
     */
    private function buildEvangelisticImpact(Collection $trainings): array
    {
        $summary = [
            'gospel_explained' => 0,
            'people_reached' => 0,
            'decisions' => 0,
            'interested' => 0,
            'rejections' => 0,
            'scheduled_visits' => 0,
        ];

        foreach ($trainings as $training) {
            $stpSummary = $this->stpMetrics->buildTrainingSummary($training);

            $summary['gospel_explained'] += (int) $stpSummary['evangelho_explicado'];
            $summary['people_reached'] += (int) $stpSummary['pessoas_ouviram'];
            $summary['decisions'] += (int) $stpSummary['decisao'];
            $summary['interested'] += (int) $stpSummary['sem_decisao_interessado'];
            $summary['rejections'] += (int) $stpSummary['rejeicao'];
            $summary['scheduled_visits'] += (int) $stpSummary['visita_agendada'];
        }

        return [
            ['key' => 'gospel_explained', 'label' => 'Evangelho explicado', 'value' => $summary['gospel_explained']],
            ['key' => 'people_reached', 'label' => 'Pessoas alcançadas', 'value' => $summary['people_reached']],
            ['key' => 'decisions', 'label' => 'Decisões', 'value' => $summary['decisions']],
            ['key' => 'interested', 'label' => 'Interessados', 'value' => $summary['interested']],
            ['key' => 'rejections', 'label' => 'Rejeições', 'value' => $summary['rejections']],
            ['key' => 'scheduled_visits', 'label' => 'Visitas agendadas', 'value' => $summary['scheduled_visits']],
        ];
    }

    /**
     * @param  Collection<int, Training>  $trainings
     * @param  Collection<int, Collection<int, StpApproach>>  $approachesByTraining
     * @return array<string, mixed>
     */
    private function buildDiscipleshipSummary(Collection $trainings, Collection $approachesByTraining): array
    {
        $trainingIds = $trainings->pluck('id')->all();

        $summary = $this->discipleshipMetrics->summarizeParallelTrack(
            $approachesByTraining
                ->filter(fn (Collection $approaches, int|string $trainingId): bool => in_array((int) $trainingId, $trainingIds, true))
                ->flatten(1),
        );

        return [
            ...$summary,
            'cards' => [
                ['key' => 'people_in_follow_up', 'label' => 'Pessoas em acompanhamento', 'value' => $summary['people_in_follow_up']],
                ['key' => 'started', 'label' => 'Acompanhamentos iniciados', 'value' => $summary['started']],
                ['key' => 'completed', 'label' => 'Acompanhamentos concluídos', 'value' => $summary['completed']],
                ['key' => 'local_church_referrals', 'label' => 'Encaminhamentos à igreja local', 'value' => $summary['local_church_referrals']],
                ['key' => 'pending_follow_ups', 'label' => 'Follow-ups pendentes', 'value' => $summary['pending_follow_ups']],
                ['key' => 'next_steps_registered', 'label' => 'Próximos passos registrados', 'value' => $summary['next_steps_registered']],
            ],
        ];
    }

    /**
     * @param  Collection<int, Training>  $trainings
     * @param  Collection<int, Collection<int, StpApproach>>  $approachesByTraining
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function buildOperationalBlocks(Collection $trainings, Collection $approachesByTraining, CarbonImmutable $today): array
    {
        $mapped = $trainings
            ->map(function (Training $training) use ($approachesByTraining, $today): array {
                $discipleshipSummary = $this->discipleshipMetrics->summarizeParallelTrack(
                    $approachesByTraining->get($training->id, collect()),
                );
                $stpSummary = $this->stpMetrics->buildTrainingSummary($training);
                $firstDate = $this->firstEventDate($training);
                $requiredMentors = max(1, (int) ceil(max(1, (int) $training->students_count) / 4));
                $completionRate = $stpSummary['sessoes_previstas'] > 0
                    ? ((int) $stpSummary['sessoes_concluidas'] / (int) $stpSummary['sessoes_previstas'])
                    : 1;

                return [
                    'training' => $training,
                    'label' => $this->trainingLabel($training),
                    'route' => route('app.teacher.trainings.show', $training),
                    'registrations_route' => route('app.teacher.trainings.registrations', $training),
                    'stp_route' => route('app.teacher.trainings.stp.approaches', $training),
                    'schedule_route' => route('app.teacher.trainings.schedule', $training),
                    'statistics_route' => route('app.teacher.trainings.statistics', $training),
                    'first_date' => $firstDate?->format('d/m/Y'),
                    'is_future' => $firstDate?->greaterThanOrEqualTo($today) ?? false,
                    'has_schedule_issue' => $this->hasScheduleIssue($training),
                    'has_registration_issue' => $training->students
                        ->contains(fn (User $student): bool => $this->registrationMetrics->hasChurchIssue($student)),
                    'mentor_shortage' => (int) $training->mentors_count < $requiredMentors,
                    'mentor_shortage_context' => sprintf('%d/%d mentores', (int) $training->mentors_count, $requiredMentors),
                    'stp_completion_low' => $stpSummary['sessoes_previstas'] > 0 && $completionRate < 0.6,
                    'stp_completion_context' => sprintf('%d/%d sessões', (int) $stpSummary['sessoes_concluidas'], (int) $stpSummary['sessoes_previstas']),
                    'discipleship_without_continuity' => $discipleshipSummary['people_in_follow_up'] > 0
                        && (
                            $discipleshipSummary['pending_follow_ups'] > 0
                            || $discipleshipSummary['next_steps_registered'] < $discipleshipSummary['people_in_follow_up']
                        ),
                    'discipleship_context' => sprintf(
                        '%d pessoas, %d follow-ups pendentes',
                        (int) $discipleshipSummary['people_in_follow_up'],
                        (int) $discipleshipSummary['pending_follow_ups'],
                    ),
                ];
            })
            ->sortBy(fn (array $item): string => $item['first_date'] ? CarbonImmutable::createFromFormat('d/m/Y', $item['first_date'])->format('Y-m-d') : '9999-12-31')
            ->values();

        return [
            'nextTrainings' => $mapped->where('is_future', true)->take(5)->values()->all(),
            'pendingTrainings' => $mapped
                ->filter(fn (array $item): bool => $item['has_schedule_issue'] || $item['has_registration_issue'])
                ->take(5)
                ->values()
                ->all(),
            'mentorShortageTrainings' => $mapped->where('mentor_shortage', true)->take(5)->values()->all(),
            'lowStpCompletionTrainings' => $mapped->where('stp_completion_low', true)->take(5)->values()->all(),
            'discipleshipWithoutContinuityTrainings' => $mapped->where('discipleship_without_continuity', true)->take(5)->values()->all(),
        ];
    }

    /**
     * @param  Collection<int, Training>  $trainings
     * @param  Collection<int, Collection<int, StpApproach>>  $approachesByTraining
     * @return array<int, array<string, mixed>>
     */
    private function buildCharts(
        DashboardPeriod $period,
        CarbonImmutable $rangeStart,
        Collection $trainings,
        Collection $approachesByTraining,
        array $discipleshipSummary,
    ): array {
        $months = $period->months();
        $series = [];

        for ($index = 0; $index < $months; $index++) {
            $month = $rangeStart->addMonths($index);
            $registrations = $trainings
                ->filter(function (Training $training) use ($month): bool {
                    $firstDate = $this->firstEventDate($training);

                    if ($firstDate === null) {
                        return CarbonImmutable::parse($training->created_at)->isSameMonth($month);
                    }

                    return $firstDate->isSameMonth($month);
                })
                ->sum('students_count');

            $series[] = new TimeSeriesPointData(
                x: $month->toDateString(),
                y: $registrations,
            );
        }

        $statusCounts = collect(TrainingStatus::cases())
            ->mapWithKeys(fn (TrainingStatus $status): array => [
                $status->label() => $trainings->filter(fn (Training $training): bool => $training->status === $status)->count(),
            ]);

        $paid = $trainings->sum(fn (Training $training): int => $this->financeMetrics->build($training)['paidStudentsCount']);
        $receiptPending = $trainings->sum(function (Training $training): int {
            return $training->students
                ->filter(fn (User $student): bool => $this->registrationMetrics->hasPaymentReceipt($student) && ! (bool) $student->pivot?->payment)
                ->count();
        });
        $unpaid = max(0, $trainings->sum('students_count') - $paid - $receiptPending);

        $stpBars = $this->buildEvangelisticImpact($trainings);
        $churchRanking = $trainings
            ->flatMap(fn (Training $training) => $training->students)
            ->groupBy(fn (User $student): string => $this->registrationMetrics->resolveChurchLabel($student))
            ->map(fn (Collection $students): int => $students->count())
            ->sortDesc()
            ->take(6);

        return [
            $this->chartPayloadBuilder->timeSeries(
                id: 'teacher-registrations-line',
                title: 'Inscrições por período',
                datasets: [
                    new ChartDatasetData(
                        label: 'Inscrições',
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
                    'xAxis' => ['unit' => $months <= 3 ? 'week' : 'month'],
                    'valueSuffix' => ' inscrições',
                ],
            )->toArray(),
            $this->chartPayloadBuilder->bar(
                id: 'teacher-trainings-status',
                title: 'Treinamentos por status',
                labels: $statusCounts->keys()->all(),
                datasets: [
                    new ChartDatasetData(
                        label: 'Treinamentos',
                        data: $statusCounts->values()->all(),
                        backgroundColor: [
                            'rgba(245, 158, 11, 0.82)',
                            'rgba(14, 165, 233, 0.82)',
                            'rgba(239, 68, 68, 0.82)',
                            'rgba(16, 185, 129, 0.82)',
                        ],
                        borderColor: [
                            'rgb(180, 83, 9)',
                            'rgb(2, 132, 199)',
                            'rgb(185, 28, 28)',
                            'rgb(5, 150, 105)',
                        ],
                    ),
                ],
                options: [
                    'valueSuffix' => ' treinamentos',
                ],
            )->toArray(),
            $this->chartPayloadBuilder->doughnut(
                id: 'teacher-financial-status',
                title: 'Situação financeira',
                labels: ['Pagantes', 'Com comprovante pendente', 'Pendentes'],
                datasets: [
                    new ChartDatasetData(
                        label: 'Inscrições',
                        data: [$paid, $receiptPending, $unpaid],
                        backgroundColor: [
                            'rgba(16, 185, 129, 0.85)',
                            'rgba(245, 158, 11, 0.85)',
                            'rgba(148, 163, 184, 0.85)',
                        ],
                        borderColor: [
                            'rgb(5, 150, 105)',
                            'rgb(180, 83, 9)',
                            'rgb(71, 85, 105)',
                        ],
                    ),
                ],
                options: [
                    'legendPosition' => 'bottom',
                    'valueSuffix' => ' inscrições',
                ],
            )->toArray(),
            $this->chartPayloadBuilder->bar(
                id: 'teacher-stp-results',
                title: 'Resultados STP',
                labels: collect($stpBars)->pluck('label')->all(),
                datasets: [
                    new ChartDatasetData(
                        label: 'STP',
                        data: collect($stpBars)->pluck('value')->all(),
                        backgroundColor: 'rgba(249, 115, 22, 0.78)',
                        borderColor: 'rgb(194, 65, 12)',
                    ),
                ],
                options: [
                    'valueSuffix' => ' registros',
                ],
            )->toArray(),
            $this->chartPayloadBuilder->bar(
                id: 'teacher-discipleship-results',
                title: 'Resultados de discipulado',
                labels: ['Em acompanhamento', 'Concluídos', 'Encaminhados', 'Pendentes'],
                datasets: [
                    new ChartDatasetData(
                        label: 'Discipulado',
                        data: [
                            (int) $discipleshipSummary['people_in_follow_up'],
                            (int) $discipleshipSummary['completed'],
                            (int) $discipleshipSummary['local_church_referrals'],
                            (int) $discipleshipSummary['pending_follow_ups'],
                        ],
                        backgroundColor: [
                            'rgba(8, 145, 178, 0.82)',
                            'rgba(34, 197, 94, 0.82)',
                            'rgba(99, 102, 241, 0.82)',
                            'rgba(245, 158, 11, 0.82)',
                        ],
                        borderColor: [
                            'rgb(14, 116, 144)',
                            'rgb(22, 163, 74)',
                            'rgb(79, 70, 229)',
                            'rgb(180, 83, 9)',
                        ],
                    ),
                ],
                options: [
                    'valueSuffix' => ' acompanhamentos',
                ],
            )->toArray(),
            $this->chartPayloadBuilder->bar(
                id: 'teacher-church-ranking',
                title: 'Ranking de igrejas que mais enviaram alunos',
                labels: $churchRanking->keys()->all(),
                datasets: [
                    new ChartDatasetData(
                        label: 'Inscritos',
                        data: $churchRanking->values()->all(),
                        backgroundColor: 'rgba(37, 99, 235, 0.78)',
                        borderColor: 'rgb(29, 78, 216)',
                    ),
                ],
                options: [
                    'valueSuffix' => ' alunos',
                ],
            )->toArray(),
        ];
    }

    /**
     * @return array<int, array{label: string, href: string}>
     */
    private function buildQuickActions(?Training $nextTraining): array
    {
        $actions = [
            ['label' => 'Criar treinamento', 'href' => route('app.teacher.trainings.create')],
            ['label' => 'Planejamento', 'href' => route('app.teacher.trainings.planning')],
            ['label' => 'Agendados', 'href' => route('app.teacher.trainings.scheduled')],
        ];

        if ($nextTraining !== null) {
            $actions[] = ['label' => 'Próximo treinamento', 'href' => route('app.teacher.trainings.show', $nextTraining)];
            $actions[] = ['label' => 'STP do próximo', 'href' => route('app.teacher.trainings.stp.approaches', $nextTraining)];
        }

        return $actions;
    }

    /**
     * @return array{key: string, label: string, value: int, description: string}
     */
    private function kpi(string $key, string $label, int $value, string $description): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'value' => $value,
            'description' => $description,
        ];
    }

    private function trainingLabel(Training $training): string
    {
        return trim((string) (($training->course?->type ? $training->course->type.' - ' : '').($training->course?->name ?? 'Treinamento')));
    }

    private function hasScheduleIssue(Training $training): bool
    {
        if ($training->eventDates->isEmpty()) {
            return true;
        }

        return ! DayScheduleHelper::hasAllDaysMatch($training->eventDates, $training->scheduleItems);
    }

    private function firstEventDate(Training $training): ?CarbonImmutable
    {
        $date = $training->eventDates->first()?->date;

        return $date ? CarbonImmutable::parse((string) $date) : null;
    }
}
