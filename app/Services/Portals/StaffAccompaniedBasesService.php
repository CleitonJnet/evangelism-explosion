<?php

namespace App\Services\Portals;

use App\Enums\EventReportStatus;
use App\Enums\EventReportType;
use App\Models\Church;
use App\Models\EventReport;
use App\Models\EventReportReview;
use App\Models\Training;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class StaffAccompaniedBasesService
{
    /**
     * @return array<string, mixed>
     */
    public function buildOverview(User $user): array
    {
        $items = $this->baseItems($user);

        return [
            'counts' => [
                'bases' => $items->count(),
                'healthy' => $items->where('health.key', 'healthy')->count(),
                'attention' => $items->filter(fn (array $item): bool => in_array($item['health']['key'], ['attention', 'follow_up'], true))->count(),
                'pending_reports' => $items->sum('pending_reports_count'),
                'follow_up' => $items->sum('follow_up_count'),
            ],
            'spotlight' => $items
                ->sortByDesc(fn (array $item): string => sprintf(
                    '%04d-%04d-%04d-%s',
                    (int) $item['follow_up_count'],
                    (int) $item['pending_reports_count'],
                    (int) $item['awaiting_review_count'],
                    (string) ($item['last_event_sort'] ?? '0000-00-00')
                ))
                ->take(5)
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function buildIndex(User $user): array
    {
        $items = $this->baseItems($user);

        return [
            'scope' => $this->scopeLabel($user),
            'counts' => [
                'bases' => $items->count(),
                'healthy' => $items->where('health.key', 'healthy')->count(),
                'attention' => $items->filter(fn (array $item): bool => in_array($item['health']['key'], ['attention', 'follow_up'], true))->count(),
                'pending_reports' => $items->sum('pending_reports_count'),
                'follow_up' => $items->sum('follow_up_count'),
            ],
            'items' => $items->values()->all(),
        ];
    }

    public function canAccessChurch(User $user, Church $church): bool
    {
        if ($this->hasInstitutionalScope($user)) {
            return true;
        }

        return $this->contextualChurchIds($user)->contains($church->id);
    }

    /**
     * @return array<string, mixed>
     */
    public function buildShow(User $user, Church $church): array
    {
        $church = $this->loadChurch($user, $church);
        $trainings = $church->trainings
            ->sortByDesc(fn (Training $training): string => $this->trainingSortDate($training))
            ->values();

        $trainingSummaries = $trainings
            ->map(fn (Training $training): array => $this->summarizeTraining($training))
            ->values();

        $pendingItems = $trainingSummaries
            ->filter(fn (array $training): bool => in_array($training['status']['key'], ['pending_submission', 'awaiting_review', 'follow_up'], true))
            ->values();

        return [
            'scope' => $this->scopeLabel($user),
            'church' => [
                'id' => $church->id,
                'name' => $church->name,
                'city' => $church->city,
                'state' => $church->state,
                'pastor' => $church->pastor,
                'contact' => $church->contact,
                'contact_phone' => $church->contact_phone,
                'contact_email' => $church->contact_email,
                'host_status' => $church->hostChurch ? 'Base anfitria configurada' : 'Base sem configuracao de anfitria',
                'fieldworkers' => $church->missionaries
                    ->map(fn (User $fieldworker): array => [
                        'id' => $fieldworker->id,
                        'name' => $fieldworker->name,
                        'is_current_user' => (int) $fieldworker->id === (int) $user->id,
                    ])
                    ->values()
                    ->all(),
            ],
            'health' => $this->healthSummary($trainingSummaries),
            'counts' => [
                'events_total' => $trainings->count(),
                'events_completed' => $trainingSummaries->where('timing', 'completed')->count(),
                'events_upcoming' => $trainingSummaries->where('timing', 'upcoming')->count(),
                'reports_received' => $trainingSummaries->sum('received_reports_count'),
                'pending_reports' => $trainingSummaries->sum('pending_sources_count'),
                'follow_up' => $trainingSummaries->sum('follow_up_count'),
            ],
            'reports' => $trainingSummaries
                ->filter(fn (array $training): bool => $training['received_reports_count'] > 0 || $training['timing'] === 'completed')
                ->take(8)
                ->values()
                ->all(),
            'pending_items' => $pendingItems->all(),
            'completed_events' => $trainingSummaries
                ->where('timing', 'completed')
                ->take(6)
                ->values()
                ->all(),
            'upcoming_events' => $trainingSummaries
                ->where('timing', 'upcoming')
                ->take(6)
                ->values()
                ->all(),
            'fieldworker_scope' => [
                'is_contextual' => ! $this->hasInstitutionalScope($user),
                'label' => $this->hasInstitutionalScope($user) ? 'Governanca institucional' : 'Acompanhamento contextual',
                'description' => $this->hasInstitutionalScope($user)
                    ? 'O Staff acompanha esta base em nivel de governanca, indicadores e leitura cruzada dos relatos.'
                    : 'Como fieldworker, seu papel aqui e acompanhar a base, ler sinais do campo e conectar Staff e operacao local sem assumir a administracao total da base.',
                'permissions' => [
                    'Pode ler a saude geral da base',
                    'Pode acompanhar eventos, relatos e pendencias',
                    'Nao substitui a gestao local da base por padrao',
                ],
            ],
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function baseItems(User $user): Collection
    {
        return $this->accompaniedChurchesQuery($user)
            ->get()
            ->map(fn (Church $church): array => $this->summarizeChurch($church))
            ->sortByDesc(fn (array $item): string => sprintf(
                '%04d-%04d-%04d-%s',
                (int) $item['follow_up_count'],
                (int) $item['pending_reports_count'],
                (int) $item['awaiting_review_count'],
                (string) ($item['last_event_sort'] ?? '0000-00-00')
            ))
            ->values();
    }

    private function loadChurch(User $user, Church $church): Church
    {
        $query = $this->accompaniedChurchesQuery($user);

        return $query->whereKey($church->id)->firstOrFail();
    }

    private function accompaniedChurchesQuery(User $user): Builder
    {
        return Church::query()
            ->with([
                'hostChurch',
                'missionaries:id,name',
                'trainings' => fn ($query) => $query
                    ->with([
                        'course:id,name,type',
                        'teacher:id,name',
                        'eventDates' => fn ($eventDates) => $eventDates->orderBy('date')->orderBy('start_time'),
                        'churchEventReport.sections',
                        'churchEventReport.reviews.reviewer',
                        'teacherEventReport.sections',
                        'teacherEventReport.reviews.reviewer',
                    ])
                    ->orderByDesc('id'),
            ])
            ->when(
                ! $this->hasInstitutionalScope($user),
                fn (Builder $query) => $query->whereIn('id', $this->contextualChurchIds($user)->all())
            )
            ->where(function (Builder $query): void {
                $query
                    ->whereHas('trainings')
                    ->orWhereHas('missionaries')
                    ->orWhereHas('hostChurch');
            })
            ->orderBy('name');
    }

    /**
     * @return Collection<int, int>
     */
    private function contextualChurchIds(User $user): Collection
    {
        $missionaryChurchIds = $user->relationLoaded('churches')
            ? $user->churches->pluck('id')
            : $user->churches()->pluck('churches.id');

        return collect([$user->church_id])
            ->merge($missionaryChurchIds)
            ->filter()
            ->map(static fn (mixed $churchId): int => (int) $churchId)
            ->unique()
            ->values();
    }

    private function hasInstitutionalScope(User $user): bool
    {
        return $user->hasRole('Director') || $user->hasRole('Board');
    }

    private function scopeLabel(User $user): string
    {
        return $this->hasInstitutionalScope($user)
            ? 'Governanca institucional'
            : 'Acompanhamento contextual do fieldworker';
    }

    /**
     * @return array<string, mixed>
     */
    private function summarizeChurch(Church $church): array
    {
        $trainings = $church->trainings
            ->sortByDesc(fn (Training $training): string => $this->trainingSortDate($training))
            ->values();

        $trainingSummaries = $trainings
            ->map(fn (Training $training): array => $this->summarizeTraining($training))
            ->values();

        $health = $this->healthSummary($trainingSummaries);
        $lastTraining = $trainings->first();

        return [
            'church_id' => $church->id,
            'name' => $church->name,
            'location' => trim(collect([$church->city, $church->state])->filter()->implode(' - ')) ?: 'Localidade nao informada',
            'fieldworkers' => $church->missionaries->pluck('name')->values()->all(),
            'fieldworkers_count' => $church->missionaries->count(),
            'events_total' => $trainings->count(),
            'completed_events_count' => $trainingSummaries->where('timing', 'completed')->count(),
            'upcoming_events_count' => $trainingSummaries->where('timing', 'upcoming')->count(),
            'reports_received_count' => $trainingSummaries->sum('received_reports_count'),
            'pending_reports_count' => $trainingSummaries->sum('pending_sources_count'),
            'awaiting_review_count' => $trainingSummaries->where('status.key', 'awaiting_review')->count(),
            'follow_up_count' => $trainingSummaries->sum('follow_up_count'),
            'health' => $health,
            'last_event_label' => $lastTraining ? $this->scheduleSummary($lastTraining) : 'Sem eventos registrados',
            'last_event_sort' => $lastTraining ? $this->trainingSortDate($lastTraining) : '0000-00-00',
            'detail_route' => route('app.portal.staff.bases.show', $church),
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $trainingSummaries
     * @return array{key: string, label: string, tone: string, description: string}
     */
    private function healthSummary(Collection $trainingSummaries): array
    {
        $followUpCount = $trainingSummaries->sum('follow_up_count');
        $pendingReportsCount = $trainingSummaries->sum('pending_sources_count');
        $awaitingReviewCount = $trainingSummaries->where('status.key', 'awaiting_review')->count();

        if ($followUpCount > 0) {
            return [
                'key' => 'follow_up',
                'label' => 'Follow-up ativo',
                'tone' => 'amber',
                'description' => 'Ha sinalizacao institucional aberta nesta base.',
            ];
        }

        if ($pendingReportsCount > 0 || $awaitingReviewCount > 0) {
            return [
                'key' => 'attention',
                'label' => 'Pede atencao',
                'tone' => 'amber',
                'description' => 'Existem relatos pendentes ou aguardando leitura.',
            ];
        }

        if ($trainingSummaries->isNotEmpty()) {
            return [
                'key' => 'healthy',
                'label' => 'Saudavel',
                'tone' => 'emerald',
                'description' => 'Acompanhamento sem pendencias institucionais no momento.',
            ];
        }

        return [
            'key' => 'monitoring',
            'label' => 'Em monitoramento',
            'tone' => 'sky',
            'description' => 'Base sem historico suficiente para leitura de saude.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function summarizeTraining(Training $training): array
    {
        $churchSource = $this->buildSourceSummary($training->churchEventReport, EventReportType::Church);
        $teacherSource = $this->buildSourceSummary($training->teacherEventReport, EventReportType::Teacher);
        $sources = collect([$churchSource, $teacherSource]);
        $latestReview = $this->latestReviewForTraining($training);
        $followUpRequired = (bool) data_get($latestReview?->payload, 'follow_up_required', false);
        $lastEventDate = $training->eventDates->last();
        $now = now();
        $lastEventAt = $lastEventDate?->date ? Carbon::parse((string) $lastEventDate->date) : null;

        $status = match (true) {
            $followUpRequired => ['key' => 'follow_up', 'label' => 'Follow-up sinalizado', 'tone' => 'amber'],
            $sources->contains(fn (array $source): bool => $source['is_pending_submission']) => ['key' => 'pending_submission', 'label' => 'Pendente de envio', 'tone' => 'amber'],
            $sources->contains(fn (array $source): bool => $source['status_key'] === 'submitted') => ['key' => 'awaiting_review', 'label' => 'Aguardando leitura', 'tone' => 'sky'],
            $sources->every(fn (array $source): bool => $source['status_key'] === 'reviewed') && $sources->isNotEmpty() => ['key' => 'reviewed', 'label' => 'Governado', 'tone' => 'emerald'],
            default => ['key' => 'monitoring', 'label' => 'Em acompanhamento', 'tone' => 'sky'],
        };

        return [
            'id' => $training->id,
            'title' => trim(sprintf('%s%s', (string) ($training->course?->type ?? 'Treinamento'), $training->course?->name ? ': '.$training->course->name : '')),
            'teacher_name' => $training->teacher?->name ?? 'Professor nao informado',
            'schedule_summary' => $this->scheduleSummary($training),
            'status' => $status,
            'timing' => $lastEventAt && $lastEventAt->isFuture() ? 'upcoming' : 'completed',
            'sources' => [$churchSource, $teacherSource],
            'received_reports_count' => $sources->filter(fn (array $source): bool => $source['is_received'])->count(),
            'pending_sources_count' => $sources->filter(fn (array $source): bool => $source['is_pending_submission'])->count(),
            'follow_up_count' => $followUpRequired ? 1 : 0,
            'latest_review_comment' => $this->nullableString($latestReview?->comment),
            'comparison_route' => $lastEventAt && $lastEventAt->lessThanOrEqualTo($now) ? route('app.portal.staff.trainings.reports', $training) : null,
            'classification' => $this->classificationLabel(data_get($latestReview?->payload, 'classification')),
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
            && blank(trim((string) $report->summary))
            && ($report->sections->isEmpty());

        if (! $report instanceof EventReport || $emptyDraft) {
            return [
                'label' => $label,
                'status_key' => 'missing',
                'status_label' => 'Nao recebido',
                'tone' => 'amber',
                'is_received' => false,
                'is_pending_submission' => true,
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
            'status_key' => $status['key'],
            'status_label' => $status['label'],
            'tone' => $status['tone'],
            'is_received' => in_array($report->status, [EventReportStatus::Submitted, EventReportStatus::NeedsRevision, EventReportStatus::Reviewed], true),
            'is_pending_submission' => in_array($report->status, [EventReportStatus::Draft, EventReportStatus::NeedsRevision], true),
        ];
    }

    private function latestReviewForTraining(Training $training): ?EventReportReview
    {
        return collect([$training->churchEventReport, $training->teacherEventReport])
            ->filter(fn (?EventReport $report): bool => $report instanceof EventReport)
            ->flatMap(fn (EventReport $report): Collection => $report->reviews->take(1))
            ->sortByDesc(fn (EventReportReview $review): string => ($review->reviewed_at?->format('Y-m-d H:i:s') ?? '').'-'.$review->id)
            ->first();
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
            return sprintf('%s%s', $firstDay->format('d/m/Y'), $firstDate->start_time ? ' · '.(string) $firstDate->start_time : '');
        }

        return sprintf('%s a %s', $firstDay->format('d/m/Y'), $lastDay->format('d/m/Y'));
    }

    private function trainingSortDate(Training $training): string
    {
        $lastDate = $training->eventDates->last();

        return ($lastDate?->date ? Carbon::parse((string) $lastDate->date)->format('Y-m-d') : '0000-00-00')
            .' '
            .((string) ($lastDate?->start_time ?? '00:00:00'));
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
}
