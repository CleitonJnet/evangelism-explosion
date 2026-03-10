<?php

namespace App\Services\Metrics;

use App\Enums\StpApproachResult;
use App\Enums\StpApproachStatus;
use App\Models\StpApproach;
use App\Models\Training;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TrainingStpMetricsService
{
    /**
     * @return array{
     *     sessoes_concluidas: int,
     *     sessoes_previstas: int,
     *     evangelho_explicado: int,
     *     pessoas_ouviram: int,
     *     decisao: int,
     *     sem_decisao_interessado: int,
     *     rejeicao: int,
     *     para_seguranca_ja_e_crente: int,
     *     visita_agendada: int
     * }
     */
    public function buildTrainingSummary(Training $training): array
    {
        $training->loadMissing('stpSessions');

        $summary = [
            'sessoes_concluidas' => 0,
            'sessoes_previstas' => $training->stpSessions->count(),
            'evangelho_explicado' => 0,
            'pessoas_ouviram' => 0,
            'decisao' => 0,
            'sem_decisao_interessado' => 0,
            'rejeicao' => 0,
            'para_seguranca_ja_e_crente' => 0,
            'visita_agendada' => 0,
        ];

        $completedStatuses = [
            StpApproachStatus::Done->value,
            StpApproachStatus::Reviewed->value,
        ];

        $summary['sessoes_concluidas'] = $training->stpSessions()
            ->whereHas('approaches', fn (Builder $query) => $query->whereIn('status', $completedStatuses))
            ->count();

        $approaches = StpApproach::query()
            ->where('training_id', $training->id)
            ->whereIn('status', $completedStatuses)
            ->get();

        foreach ($approaches as $approach) {
            $summary['evangelho_explicado'] += (int) ($approach->gospel_explained_times ?? 0);

            $listeners = collect(data_get($approach->payload, 'listeners', []))
                ->filter(fn (mixed $listener): bool => is_array($listener))
                ->values();

            if ($listeners->isNotEmpty()) {
                $summary['pessoas_ouviram'] += $listeners->count();

                foreach ($listeners as $listener) {
                    $resultKey = data_get($listener, 'result');
                    $this->incrementTrainingSummaryResultTotals($summary, is_string($resultKey) ? $resultKey : null);
                }
            } else {
                $summary['pessoas_ouviram'] += (int) ($approach->people_count ?? 0);

                $resultKey = $approach->result instanceof StpApproachResult
                    ? $approach->result->value
                    : null;

                $this->incrementTrainingSummaryResultTotals($summary, $resultKey);
            }

            if ($approach->follow_up_scheduled_at !== null) {
                $summary['visita_agendada']++;
            }
        }

        return $summary;
    }

    /**
     * @param  Collection<int, StpApproach>  $approaches
     * @return array{
     *     total: int,
     *     concluidas: int,
     *     revisadas: int,
     *     decisoes: int,
     *     acompanhamentos: int
     * }
     */
    public function summarizeApproaches(Collection $approaches): array
    {
        $summary = [
            'total' => $approaches->count(),
            'concluidas' => 0,
            'revisadas' => 0,
            'decisoes' => 0,
            'acompanhamentos' => 0,
        ];

        foreach ($approaches as $approach) {
            $status = $approach->status instanceof StpApproachStatus
                ? $approach->status->value
                : (string) $approach->status;
            $result = $approach->result instanceof StpApproachResult
                ? $approach->result->value
                : (string) $approach->result;

            if ($status === StpApproachStatus::Done->value) {
                $summary['concluidas']++;
            }

            if ($status === StpApproachStatus::Reviewed->value) {
                $summary['revisadas']++;
            }

            if ($result === StpApproachResult::Decision->value) {
                $summary['decisoes']++;
            }

            if ($approach->follow_up_scheduled_at !== null) {
                $summary['acompanhamentos']++;
            }
        }

        return $summary;
    }

    /**
     * @param  array<string, int>  $summary
     */
    private function incrementTrainingSummaryResultTotals(array &$summary, ?string $resultKey): void
    {
        if ($resultKey === null) {
            return;
        }

        if ($resultKey === StpApproachResult::Decision->value) {
            $summary['decisao']++;
        }

        if ($resultKey === StpApproachResult::NoDecisionInterested->value) {
            $summary['sem_decisao_interessado']++;
        }

        if ($resultKey === StpApproachResult::Rejection->value) {
            $summary['rejeicao']++;
        }

        if ($resultKey === StpApproachResult::AlreadyChristian->value) {
            $summary['para_seguranca_ja_e_crente']++;
        }
    }
}
