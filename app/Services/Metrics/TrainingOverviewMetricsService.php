<?php

namespace App\Services\Metrics;

use App\Models\Training;

class TrainingOverviewMetricsService
{
    public function __construct(
        private TrainingRegistrationMetricsService $registrationMetrics,
        private TrainingFinanceMetricsService $financeMetrics,
        private TrainingStpMetricsService $stpMetrics,
    ) {}

    /**
     * @return array{
     *     totalRegistrations: int,
     *     totalParticipatingChurches: int,
     *     totalPastors: int,
     *     totalUsedKits: int,
     *     totalNewChurches: int,
     *     totalDecisions: int,
     *     paidStudentsCount: int,
     *     resumoStp: array{
     *         sessoes_concluidas: int,
     *         sessoes_previstas: int,
     *         evangelho_explicado: int,
     *         pessoas_ouviram: int,
     *         decisao: int,
     *         sem_decisao_interessado: int,
     *         rejeicao: int,
     *         para_seguranca_ja_e_crente: int,
     *         visita_agendada: int
     *     },
     *     totalReceivedFromRegistrations: ?string,
     *     eeMinistryBalance: ?string,
     *     hostChurchExpenseBalance: ?string
     * }
     */
    public function build(Training $training): array
    {
        $training->loadMissing(['students', 'newChurches', 'stpSessions']);

        $registrationSummary = $this->registrationMetrics->summarizeOverview($training);
        $stpSummary = $this->stpMetrics->buildTrainingSummary($training);
        $financeSummary = $this->financeMetrics->build($training);

        return [
            ...$registrationSummary,
            'totalNewChurches' => $training->newChurches()->count(),
            'totalDecisions' => $stpSummary['decisao'],
            'resumoStp' => $stpSummary,
            ...$financeSummary,
        ];
    }
}
