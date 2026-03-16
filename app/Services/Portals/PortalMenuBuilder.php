<?php

namespace App\Services\Portals;

use App\Models\User;
use App\Support\Portals\Data\PortalMenuItemData;
use App\Support\Portals\Data\PortalMenuSectionData;
use App\Support\Portals\Enums\Portal;
use Illuminate\Support\Facades\Route;

class PortalMenuBuilder
{
    public function __construct(private BasePortalNavigationService $basePortalNavigationService) {}

    /**
     * @return array<int, PortalMenuSectionData>
     */
    public function build(User $user, Portal $portal): array
    {
        if ($portal === Portal::Base) {
            $navigation = $this->basePortalNavigationService->summary($user);

            return array_values(array_filter([
                new PortalMenuSectionData('Portal Base', array_values(array_filter([
                    new PortalMenuItemData(
                        label: 'Visao geral',
                        route: 'app.portal.base.dashboard',
                        icon: 'home',
                        description: 'Leitura integrada da operacao local, treinamentos e eventos da base.',
                    ),
                    $navigation['canViewMyBase'] ? new PortalMenuItemData(
                        label: 'Minha Base',
                        route: 'app.portal.base.my-base',
                        icon: 'building-library',
                        description: 'Igreja-base, anfitriao, acervo e alertas do contexto local.',
                    ) : null,
                    $navigation['canViewBaseInventory'] ? new PortalMenuItemData(
                        label: 'Acervo da Base',
                        route: 'app.portal.base.inventory',
                        icon: 'archive-box',
                        description: 'Saldo local, entradas, uso por evento e alertas de reposicao da base.',
                    ) : null,
                    $navigation['canViewServing'] ? new PortalMenuItemData(
                        label: 'Treinamentos em que Sirvo',
                        route: 'app.portal.base.serving',
                        icon: 'presentation-chart-bar',
                        description: 'Agenda operacional, programacao pendente e relatorios do que voce serve.',
                    ) : null,
                    $navigation['canViewBaseEvents'] ? new PortalMenuItemData(
                        label: 'Eventos da Base',
                        route: 'app.portal.base.events',
                        icon: 'calendar-days',
                        description: 'Eventos hospedados pela sua base com foco na frente local.',
                    ) : null,
                ]))),
                new PortalMenuSectionData('Fluxos operacionais', array_values(array_filter([
                    $this->legacyItem($user, 'access-teacher', 'Treinamentos', 'app.teacher.trainings.index', 'book-open', 'Abrir o modulo operacional atual de treinamentos.'),
                    $this->legacyItem($user, 'manageChurches', 'Igrejas e base', $user->can('access-director') ? 'app.director.church.index' : 'app.teacher.churches.index', 'building-office', 'Gerir base, anfitriao e cadastros locais.'),
                    $this->legacyItem($user, 'access-director', 'Acervo e estoques', 'app.director.inventory.index', 'archive-box', 'Monitorar estoques, materiais e movimentacoes.'),
                    $this->legacyItem($user, 'access-teacher', 'Meu estoque', 'app.teacher.inventory.index', 'archive-box', 'Acompanhar estoques delegados e alertas locais.'),
                    $this->legacyItem($user, 'access-mentor', 'Mentoria e OJT', 'app.mentor.ojt.sessions.index', 'users', 'Continuar sessoes e acompanhamento de equipes.'),
                ]))),
                new PortalMenuSectionData('Conta', [
                    new PortalMenuItemData(
                        label: 'Selecionar portal',
                        route: 'app.start',
                        icon: 'arrows-right-left',
                        description: 'Troque de portal sem perder os acessos existentes.',
                    ),
                ]),
            ]));
        }

        if ($portal === Portal::Student) {
            return array_values(array_filter([
                new PortalMenuSectionData('Portal do aluno', [
                    new PortalMenuItemData(
                        label: 'Visao geral',
                        route: 'app.portal.student.dashboard',
                        icon: 'home',
                        description: 'Resumo da sua jornada, proximos passos e pendencias.',
                    ),
                    new PortalMenuItemData(
                        label: 'Treinamentos',
                        route: 'app.portal.student.trainings.index',
                        icon: 'calendar-days',
                        description: 'Agenda, andamento e detalhes dos seus treinamentos.',
                    ),
                    new PortalMenuItemData(
                        label: 'Historico',
                        route: 'app.portal.student.history',
                        icon: 'clock',
                        description: 'Participacoes concluídas e progresso recente.',
                    ),
                    new PortalMenuItemData(
                        label: 'Comprovantes',
                        route: 'app.portal.student.receipts',
                        icon: 'document-text',
                        description: 'Acompanhe envios, validacoes e pendencias de pagamento.',
                    ),
                    new PortalMenuItemData(
                        label: 'Certificados',
                        route: 'app.portal.student.certificates',
                        icon: 'academic-cap',
                        description: 'Area preparada para emissao e consulta futura de certificados.',
                    ),
                ]),
                new PortalMenuSectionData('Conta', [
                    new PortalMenuItemData(
                        label: 'Selecionar portal',
                        route: 'app.start',
                        icon: 'arrows-right-left',
                        description: 'Troque de portal sem perder os acessos existentes.',
                    ),
                ]),
            ]));
        }

        if ($portal === Portal::Staff) {
            return array_values(array_filter([
                new PortalMenuSectionData('Portal Staff', [
                    new PortalMenuItemData(
                        label: 'Painel',
                        route: 'app.portal.staff.dashboard',
                        icon: 'home',
                        description: 'Indicadores resumidos de governanca e supervisao dos eventos.',
                    ),
                    new PortalMenuItemData(
                        label: 'Bases acompanhadas',
                        route: 'app.portal.staff.bases.index',
                        icon: 'building-library',
                        description: 'Leitura por base da saude geral, eventos, pendencias e sinais que sobem do campo.',
                    ),
                    new PortalMenuItemData(
                        label: 'Relatorios',
                        route: 'app.portal.staff.reports.index',
                        icon: 'document-text',
                        description: 'Fila consolidada de evidencias recebidas do campo para leitura cruzada.',
                    ),
                    new PortalMenuItemData(
                        label: 'Estoque central',
                        route: 'app.portal.staff.inventory.index',
                        icon: 'archive-box',
                        description: 'Leitura institucional do acervo nacional com ponte para o modulo central atual.',
                    ),
                    new PortalMenuItemData(
                        label: 'Conselho',
                        route: 'app.portal.staff.council.index',
                        icon: 'scale',
                        description: 'Landing inicial para documentos, pautas e deliberacoes do Conselho Nacional.',
                    ),
                ]),
                new PortalMenuSectionData('Conta', [
                    new PortalMenuItemData(
                        label: 'Selecionar portal',
                        route: 'app.start',
                        icon: 'arrows-right-left',
                        description: 'Troque de portal sem perder os acessos existentes.',
                    ),
                ]),
                $this->legacySection($user, $portal),
            ]));
        }

        return array_values(array_filter([
            new PortalMenuSectionData('Portal', [
                new PortalMenuItemData(
                    label: 'Dashboard do portal',
                    route: $portal->entryRoute(),
                    icon: $portal->icon(),
                    description: 'Ponto de entrada tecnico da nova arquitetura.',
                ),
                new PortalMenuItemData(
                    label: 'Selecionar portal',
                    route: 'app.start',
                    icon: 'arrows-right-left',
                    description: 'Volta para a selecao principal de portais sem remover os acessos legados.',
                ),
            ]),
            $this->legacySection($user, $portal),
        ]));
    }

    protected function legacySection(User $user, Portal $portal): ?PortalMenuSectionData
    {
        $items = match ($portal) {
            Portal::Base => [
                $this->legacyItem($user, 'access-director', 'Painel legado do diretor', 'app.director.dashboard', 'briefcase'),
                $this->legacyItem($user, 'access-teacher', 'Painel legado do professor', 'app.teacher.dashboard', 'presentation-chart-bar'),
                $this->legacyItem($user, 'access-facilitator', 'Painel legado do facilitador', 'app.facilitator.dashboard', 'user-group'),
                $this->legacyItem($user, 'access-mentor', 'Painel legado do mentor', 'app.mentor.dashboard', 'users'),
            ],
            Portal::Staff => [
                $this->legacyItem($user, 'access-board', 'Painel legado do board', 'app.board.dashboard', 'scale'),
                $this->legacyItem($user, 'access-director', 'Painel legado do diretor', 'app.director.dashboard', 'briefcase'),
                $this->legacyItem($user, 'access-fieldworker', 'Painel legado do field worker', 'app.fieldworker.dashboard', 'map'),
            ],
            Portal::Student => [
                $this->legacyItem($user, 'access-student', 'Painel legado do aluno', 'app.student.dashboard', 'academic-cap'),
                $this->legacyItem($user, 'access-student', 'Treinamentos do aluno', 'app.student.training.index', 'book-open'),
            ],
        };

        $items = array_values(array_filter($items));

        if ($items === []) {
            return null;
        }

        return new PortalMenuSectionData('Acessos legados', $items);
    }

    protected function legacyItem(
        User $user,
        string $ability,
        string $label,
        string $route,
        string $icon,
        ?string $description = null,
    ): ?PortalMenuItemData {
        if (! $user->can($ability) || ! Route::has($route)) {
            return null;
        }

        return new PortalMenuItemData(
            label: $label,
            route: $route,
            icon: $icon,
            description: $description ?? 'Mantido para compatibilidade com a arquitetura atual.',
        );
    }
}
