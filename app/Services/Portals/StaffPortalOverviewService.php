<?php

namespace App\Services\Portals;

use App\Models\User;
use App\Services\EventReports\StaffEventReportGovernanceService;

class StaffPortalOverviewService
{
    public function __construct(
        private StaffEventReportGovernanceService $governanceService,
        private StaffAccompaniedBasesService $accompaniedBasesService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(User $user): array
    {
        $overview = $this->governanceService->buildOverview($user);
        $basesOverview = $this->accompaniedBasesService->buildOverview($user);

        return [
            ...$overview,
            'bases' => $basesOverview,
            'shortcuts' => [
                [
                    'label' => 'Painel de Conselho',
                    'description' => 'Area inicial do Conselho Nacional com trilhas para documentos, pautas e deliberacoes.',
                    'route' => route('app.portal.staff.council.index'),
                ],
                [
                    'label' => 'Bases acompanhadas',
                    'description' => 'Painel de leitura da saude, pendencias e relatorios por base acompanhada.',
                    'route' => route('app.portal.staff.bases.index'),
                ],
                [
                    'label' => 'Relatorios recebidos',
                    'description' => 'Fila consolidada de evidencias enviadas pelo campo e seus vazios de envio.',
                    'route' => route('app.portal.staff.reports.index'),
                ],
                [
                    'label' => 'Pendentes de envio',
                    'description' => 'Eventos concluidos em que uma ou mais fontes ainda nao chegaram ao Staff.',
                    'route' => route('app.portal.staff.reports.pending'),
                ],
                [
                    'label' => 'Estoque central',
                    'description' => 'Acompanhe o acervo nacional sem sair do portal Staff e reaproveite o modulo atual.',
                    'route' => route('app.portal.staff.inventory.index'),
                ],
                [
                    'label' => 'Follow-up institucional',
                    'description' => 'Eventos sinalizados para acompanhamento, alinhamento ou intervencao posterior.',
                    'route' => route('app.portal.staff.reports.follow-up'),
                ],
            ],
        ];
    }
}
