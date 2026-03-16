<?php

namespace App\Services\EventReports;

use App\Enums\EventReportReviewOutcome;
use App\Enums\EventReportStatus;
use App\Enums\EventReportType;
use App\Models\EventReport;
use App\Models\EventReportReview;
use App\Models\Training;
use App\Models\User;
use App\Services\Portals\StaffAccompaniedBasesService;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StaffEventReportGovernanceService
{
    public function __construct(
        private EventReportWorkflowService $workflowService,
        private EventReportReviewService $reviewService,
        private StaffAccompaniedBasesService $accompaniedBasesService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function buildOverview(User $user): array
    {
        $items = $this->queueCollection($user);

        return [
            'counts' => [
                'with_reports' => $items->filter(fn (array $item): bool => $item['received_reports_count'] > 0)->count(),
                'pending_submission' => $items->where('status_key', 'pending_submission')->count(),
                'awaiting_review' => $items->where('status_key', 'awaiting_review')->count(),
                'follow_up' => $items->where('follow_up_required', true)->count(),
                'governed' => $items->where('status_key', 'governed')->count(),
            ],
            'recent_items' => $items->take(6)->values()->all(),
            'pending_items' => $items
                ->filter(fn (array $item): bool => in_array($item['status_key'], ['pending_submission', 'awaiting_review', 'follow_up'], true))
                ->take(6)
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function buildQueue(User $user, string $filter = 'all'): array
    {
        $items = $this->queueCollection($user);
        $filteredItems = $this->applyFilter($items, $filter);

        return [
            'filter' => $filter,
            'filters' => [
                ['key' => 'all', 'label' => 'Todos', 'count' => $items->count(), 'route' => route('app.portal.staff.reports.index')],
                ['key' => 'pending', 'label' => 'Pendentes de envio', 'count' => $items->where('status_key', 'pending_submission')->count(), 'route' => route('app.portal.staff.reports.pending')],
                ['key' => 'awaiting-review', 'label' => 'Aguardando leitura', 'count' => $items->where('status_key', 'awaiting_review')->count(), 'route' => route('app.portal.staff.reports.awaiting-review')],
                ['key' => 'follow-up', 'label' => 'Follow-up sinalizado', 'count' => $items->where('follow_up_required', true)->count(), 'route' => route('app.portal.staff.reports.follow-up')],
            ],
            'counts' => [
                'items' => $filteredItems->count(),
                'pending_submission' => $items->where('status_key', 'pending_submission')->count(),
                'awaiting_review' => $items->where('status_key', 'awaiting_review')->count(),
                'follow_up' => $items->where('follow_up_required', true)->count(),
            ],
            'items' => $filteredItems->values()->all(),
        ];
    }

    public function canAccessTraining(User $user, Training $training): bool
    {
        $training = $this->loadTraining($training);

        return $this->isGovernanceVisible($training)
            && $training->church instanceof \App\Models\Church
            && $this->accompaniedBasesService->canAccessChurch($user, $training->church);
    }

    /**
     * @return array<string, mixed>
     */
    public function buildComparison(User $user, Training $training): array
    {
        $training = $this->loadTraining($training);

        if (! $this->canAccessTraining($user, $training)) {
            throw new AuthorizationException;
        }

        $queueItem = $this->buildQueueItem($training);

        return [
            'queue_item' => $queueItem,
            'findings' => $this->buildFindings($queueItem),
            'sources' => [
                $this->buildSourceDetail($training->churchEventReport, EventReportType::Church),
                $this->buildSourceDetail($training->teacherEventReport, EventReportType::Teacher),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function recordTrainingReview(
        Training $training,
        User $reviewer,
        EventReportReviewOutcome $outcome,
        ?string $comment = null,
        array $payload = [],
    ): int {
        if (! $this->canGovern($reviewer)) {
            throw new AuthorizationException;
        }

        $training = $this->loadTraining($training);

        if (! $this->canAccessTraining($reviewer, $training)) {
            throw new AuthorizationException;
        }

        $reports = $this->reviewableReports($training);

        if ($reports->isEmpty()) {
            throw ValidationException::withMessages([
                'reviewForm.action' => 'Nao ha relatorios enviados para receber leitura do Staff neste evento.',
            ]);
        }

        DB::transaction(function () use ($reports, $reviewer, $outcome, $comment, $payload): void {
            foreach ($reports as $report) {
                match ($outcome) {
                    EventReportReviewOutcome::Approved => $this->reviewService->approve($report, $reviewer, $comment, $payload),
                    EventReportReviewOutcome::ChangesRequested => $this->reviewService->requestChanges($report, $reviewer, $comment, $payload),
                    EventReportReviewOutcome::Commented => $this->reviewService->comment($report, $reviewer, $comment, $payload),
                };
            }
        });

        return $reports->count();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function queueCollection(User $user): Collection
    {
        return Training::query()
            ->with([
                'course:id,name,type',
                'church:id,name,city,state',
                'teacher:id,name',
                'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
                'churchEventReport.sections',
                'churchEventReport.reviews.reviewer',
                'teacherEventReport.sections',
                'teacherEventReport.reviews.reviewer',
            ])
            ->where(function (Builder $query): void {
                $query
                    ->whereHas('eventReports')
                    ->orWhereHas('eventDates', fn (Builder $eventDates): Builder => $eventDates->whereDate('date', '<=', now()->toDateString()));
            })
            ->get()
            ->filter(fn (Training $training): bool => $this->canAccessTraining($user, $training))
            ->map(fn (Training $training): array => $this->buildQueueItem($training))
            ->sortByDesc('sort_date')
            ->values();
    }

    private function canGovern(User $user): bool
    {
        return $user->hasRole('Board') || $user->hasRole('Director');
    }

    private function isGovernanceVisible(Training $training): bool
    {
        if ($training->churchEventReport instanceof EventReport || $training->teacherEventReport instanceof EventReport) {
            return true;
        }

        $lastDate = $training->eventDates->last();

        return $lastDate !== null
            && $lastDate->date !== null
            && Carbon::parse((string) $lastDate->date)->isPast();
    }

    private function loadTraining(Training $training): Training
    {
        return Training::query()
            ->with([
                'course:id,name,type',
                'church:id,name,city,state',
                'teacher:id,name',
                'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
                'churchEventReport.sections',
                'churchEventReport.reviews.reviewer',
                'teacherEventReport.sections',
                'teacherEventReport.reviews.reviewer',
            ])
            ->findOrFail($training->id);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function applyFilter(Collection $items, string $filter): Collection
    {
        return match ($filter) {
            'pending' => $items->where('status_key', 'pending_submission'),
            'awaiting-review' => $items->where('status_key', 'awaiting_review'),
            'follow-up' => $items->where('follow_up_required', true),
            default => $items,
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function buildQueueItem(Training $training): array
    {
        $churchSource = $this->buildSourceSummary($training->churchEventReport, EventReportType::Church);
        $teacherSource = $this->buildSourceSummary($training->teacherEventReport, EventReportType::Teacher);
        $sources = collect([$churchSource, $teacherSource]);
        $latestReview = $this->latestReviewForTraining($training);
        $lastEventDate = $training->eventDates->last();
        $followUpRequired = (bool) data_get($latestReview?->payload, 'follow_up_required', false);

        $status = match (true) {
            $followUpRequired => ['key' => 'follow_up', 'label' => 'Follow-up sinalizado', 'tone' => 'amber'],
            $sources->contains(fn (array $source): bool => $source['is_pending_submission']) => ['key' => 'pending_submission', 'label' => 'Pendente de envio', 'tone' => 'amber'],
            $sources->contains(fn (array $source): bool => $source['status_key'] === 'submitted') => ['key' => 'awaiting_review', 'label' => 'Aguardando leitura', 'tone' => 'sky'],
            $sources->every(fn (array $source): bool => $source['status_key'] === 'reviewed') => ['key' => 'governed', 'label' => 'Governado', 'tone' => 'emerald'],
            default => ['key' => 'awaiting_review', 'label' => 'Aguardando leitura', 'tone' => 'sky'],
        };

        $pendingSources = $sources
            ->filter(fn (array $source): bool => $source['is_pending_submission'])
            ->map(fn (array $source): string => $source['label'])
            ->values()
            ->all();

        return [
            'training_id' => $training->id,
            'title' => trim(sprintf('%s%s', (string) ($training->course?->type ?? 'Treinamento'), $training->course?->name ? ': '.$training->course->name : '')),
            'church_name' => $training->church?->name ?? 'Base nao informada',
            'teacher_name' => $training->teacher?->name ?? 'Professor nao informado',
            'schedule_summary' => $this->scheduleSummary($training),
            'status_key' => $status['key'],
            'status_label' => $status['label'],
            'tone' => $status['tone'],
            'sources' => [$churchSource, $teacherSource],
            'pending_sources' => $pendingSources,
            'received_reports_count' => $sources->filter(fn (array $source): bool => $source['is_received'])->count(),
            'reviewed_reports_count' => $sources->filter(fn (array $source): bool => $source['status_key'] === 'reviewed')->count(),
            'follow_up_required' => $followUpRequired,
            'classification' => $this->classificationLabel(data_get($latestReview?->payload, 'classification')),
            'latest_review_comment' => $this->nullableString($latestReview?->comment),
            'latest_reviewed_at' => $latestReview?->reviewed_at?->format('d/m/Y H:i'),
            'comparison_route' => route('app.portal.staff.trainings.reports', $training),
            'sort_date' => ($lastEventDate?->date ? Carbon::parse((string) $lastEventDate->date)->format('Y-m-d') : '0000-00-00').' '.((string) ($lastEventDate?->start_time ?? '00:00:00')),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildSourceSummary(?EventReport $report, EventReportType $type): array
    {
        $label = $type === EventReportType::Church ? 'Igreja-base' : 'Professor';
        $emptyDraft = $report instanceof EventReport
            && $report->status === EventReportStatus::Draft
            && ! $this->workflowService->hasMeaningfulContent($report);

        if (! $report instanceof EventReport || $emptyDraft) {
            return [
                'label' => $label,
                'type' => $type->value,
                'status_key' => 'missing',
                'status_label' => 'Nao recebido',
                'tone' => 'amber',
                'is_received' => false,
                'is_pending_submission' => true,
                'submitted_at' => null,
            ];
        }

        $status = match ($report->status) {
            EventReportStatus::Draft => ['key' => 'draft', 'label' => 'Em rascunho', 'tone' => 'slate'],
            EventReportStatus::NeedsRevision => ['key' => 'needs_revision', 'label' => 'Revisao solicitada', 'tone' => 'amber'],
            EventReportStatus::Reviewed => ['key' => 'reviewed', 'label' => 'Revisado pelo Staff', 'tone' => 'emerald'],
            default => ['key' => 'submitted', 'label' => 'Recebido', 'tone' => 'sky'],
        };

        return [
            'label' => $label,
            'type' => $type->value,
            'status_key' => $status['key'],
            'status_label' => $status['label'],
            'tone' => $status['tone'],
            'is_received' => in_array($report->status, [EventReportStatus::Submitted, EventReportStatus::NeedsRevision, EventReportStatus::Reviewed], true),
            'is_pending_submission' => in_array($report->status, [EventReportStatus::Draft, EventReportStatus::NeedsRevision], true),
            'submitted_at' => $report->submitted_at?->format('d/m/Y H:i'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildSourceDetail(?EventReport $report, EventReportType $type): array
    {
        $summary = $this->buildSourceSummary($report, $type);
        $sections = $report?->sections?->keyBy('key') ?? collect();
        $latestReview = $report?->reviews?->first();

        return [
            'label' => $type === EventReportType::Church ? 'Relatorio da igreja-base' : 'Relatorio do professor',
            'description' => $type === EventReportType::Church
                ? 'Evidencia local sobre participacao, acompanhamento e suporte da base anfitria.'
                : 'Evidencia ministerial sobre execucao, resultados e recomendacoes do professor responsavel.',
            'type' => $type->value,
            'status_key' => $summary['status_key'],
            'status_label' => $summary['status_label'],
            'tone' => $summary['tone'],
            'exists' => $report instanceof EventReport,
            'title' => $report?->title ?: null,
            'summary' => $report?->summary ?: null,
            'submitted_at' => $report?->submitted_at?->format('d/m/Y H:i'),
            'reviewed_at' => $report?->reviewed_at?->format('d/m/Y H:i'),
            'metrics' => $this->metricsForReport($type, $sections),
            'notes' => $this->notesForReport($type, $sections),
            'latest_review' => $latestReview ? [
                'outcome_label' => $this->outcomeLabel($latestReview->outcome),
                'comment' => $this->nullableString($latestReview->comment),
                'reviewed_at' => $latestReview->reviewed_at?->format('d/m/Y H:i'),
                'reviewer_name' => $latestReview->reviewer?->name ?? 'Staff',
                'classification' => $this->classificationLabel(data_get($latestReview->payload, 'classification')),
                'follow_up_required' => (bool) data_get($latestReview->payload, 'follow_up_required', false),
            ] : null,
            'history' => $report?->reviews?->take(4)->map(fn (EventReportReview $review): array => [
                'outcome_label' => $this->outcomeLabel($review->outcome),
                'comment' => $this->nullableString($review->comment),
                'reviewed_at' => $review->reviewed_at?->format('d/m/Y H:i'),
                'reviewer_name' => $review->reviewer?->name ?? 'Staff',
                'classification' => $this->classificationLabel(data_get($review->payload, 'classification')),
                'follow_up_required' => (bool) data_get($review->payload, 'follow_up_required', false),
            ])->values()->all() ?? [],
        ];
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    private function metricsForReport(EventReportType $type, Collection $sections): array
    {
        return match ($type) {
            EventReportType::Church => [
                ['label' => 'Inscritos previstos', 'value' => $this->stringValue(data_get($sections, 'attendance.content.registered'))],
                ['label' => 'Participantes presentes', 'value' => $this->stringValue(data_get($sections, 'attendance.content.present'))],
                ['label' => 'Decisoes registradas', 'value' => $this->stringValue(data_get($sections, 'attendance.content.decisions'))],
            ],
            EventReportType::Teacher => [
                ['label' => 'Sessoes concluidas', 'value' => $this->stringValue(data_get($sections, 'execution.content.sessions_completed'))],
                ['label' => 'Pessoas treinadas', 'value' => $this->stringValue(data_get($sections, 'execution.content.people_trained'))],
                ['label' => 'Contatos nas saidas', 'value' => $this->stringValue(data_get($sections, 'execution.content.practical_contacts'))],
            ],
        };
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    private function notesForReport(EventReportType $type, Collection $sections): array
    {
        return match ($type) {
            EventReportType::Church => [
                ['label' => 'Acompanhamento da base', 'value' => $this->nullableString(data_get($sections, 'follow_up.content.actions'))],
                ['label' => 'Destaques operacionais', 'value' => $this->nullableString(data_get($sections, 'host_highlights.content.highlights'))],
                ['label' => 'Suporte necessario', 'value' => $this->nullableString(data_get($sections, 'host_highlights.content.support_needed'))],
            ],
            EventReportType::Teacher => [
                ['label' => 'Destaques ministeriais', 'value' => $this->nullableString(data_get($sections, 'ministry_highlights.content.highlights'))],
                ['label' => 'Recomendacoes', 'value' => $this->nullableString(data_get($sections, 'next_cycle.content.recommendations'))],
                ['label' => 'Proximos passos', 'value' => $this->nullableString(data_get($sections, 'next_cycle.content.next_steps'))],
            ],
        };
    }

    /**
     * @return array<int, string>
     */
    private function buildFindings(array $queueItem): array
    {
        $findings = [];

        if ($queueItem['pending_sources'] !== []) {
            $findings[] = 'Pendencia de envio em: '.implode(', ', $queueItem['pending_sources']).'.';
        }

        if ($queueItem['received_reports_count'] === 2) {
            $findings[] = 'As duas fontes de evidencia foram recebidas e podem ser lidas lado a lado.';
        }

        if ($queueItem['follow_up_required']) {
            $findings[] = 'O ultimo registro do Staff sinalizou follow-up institucional para este evento.';
        }

        if ($queueItem['latest_review_comment']) {
            $findings[] = 'Ultima observacao do Staff: '.$queueItem['latest_review_comment'];
        }

        return $findings;
    }

    private function latestReviewForTraining(Training $training): ?EventReportReview
    {
        return collect([$training->churchEventReport, $training->teacherEventReport])
            ->filter(fn (?EventReport $report): bool => $report instanceof EventReport)
            ->flatMap(fn (EventReport $report): Collection => $report->reviews->take(1))
            ->sortByDesc(fn (EventReportReview $review): string => ($review->reviewed_at?->format('Y-m-d H:i:s') ?? '').'-'.$review->id)
            ->first();
    }

    /**
     * @return Collection<int, EventReport>
     */
    private function reviewableReports(Training $training): Collection
    {
        return collect([$training->churchEventReport, $training->teacherEventReport])
            ->filter(fn (?EventReport $report): bool => $report instanceof EventReport)
            ->filter(fn (EventReport $report): bool => in_array($report->status, [EventReportStatus::Submitted, EventReportStatus::NeedsRevision, EventReportStatus::Reviewed], true))
            ->values();
    }

    private function scheduleSummary(Training $training): string
    {
        $firstDate = $training->eventDates->first();
        $lastDate = $training->eventDates->last();

        if ($firstDate === null || $lastDate === null || $firstDate->date === null || $lastDate->date === null) {
            return 'Agenda do evento nao informada';
        }

        $firstDay = Carbon::parse((string) $firstDate->date);
        $lastDay = Carbon::parse((string) $lastDate->date);

        if ($firstDay->isSameDay($lastDay)) {
            return sprintf(
                '%s%s',
                $firstDay->format('d/m/Y'),
                $firstDate->start_time ? ' · '.(string) $firstDate->start_time : ''
            );
        }

        return sprintf(
            '%s a %s',
            $firstDay->format('d/m/Y'),
            $lastDay->format('d/m/Y'),
        );
    }

    private function outcomeLabel(EventReportReviewOutcome $outcome): string
    {
        return match ($outcome) {
            EventReportReviewOutcome::Approved => 'Aprovado',
            EventReportReviewOutcome::ChangesRequested => 'Ajustes solicitados',
            EventReportReviewOutcome::Commented => 'Comentado',
        };
    }

    private function classificationLabel(?string $classification): ?string
    {
        return match ($classification) {
            'aligned' => 'Alinhado',
            'attention' => 'Atencao',
            'critical' => 'Critico',
            default => null,
        };
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function stringValue(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }
}
