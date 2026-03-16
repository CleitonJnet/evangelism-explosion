<?php

namespace App\Services\Portals;

use App\Models\Training;
use App\Models\User;

class BasePortalNavigationService
{
    public function __construct(private PortalBaseCapabilityService $portalBaseCapabilityService) {}

    /**
     * @return array{
     *     canViewMyBase: bool,
     *     canViewServing: bool,
     *     canViewBaseEvents: bool,
     *     canViewBaseInventory: bool,
     *     primaryAreaLabel: string,
     *     primaryAreaRoute: string
     * }
     */
    public function summary(User $user): array
    {
        $canViewMyBase = $this->canViewMyBase($user);
        $canViewServing = $this->canViewServing($user);
        $canViewBaseEvents = $this->canViewBaseEvents($user);
        $canViewBaseInventory = $this->canViewBaseInventory($user);

        $primaryArea = match (true) {
            $canViewServing => ['label' => 'Treinamentos em que Sirvo', 'route' => route('app.portal.base.serving')],
            $canViewMyBase => ['label' => 'Minha Base', 'route' => route('app.portal.base.my-base')],
            $canViewBaseInventory => ['label' => 'Acervo da Base', 'route' => route('app.portal.base.inventory')],
            $canViewBaseEvents => ['label' => 'Eventos da Base', 'route' => route('app.portal.base.events')],
            default => ['label' => 'Visao geral', 'route' => route('app.portal.base.dashboard')],
        };

        return [
            'canViewMyBase' => $canViewMyBase,
            'canViewServing' => $canViewServing,
            'canViewBaseEvents' => $canViewBaseEvents,
            'canViewBaseInventory' => $canViewBaseInventory,
            'primaryAreaLabel' => $primaryArea['label'],
            'primaryAreaRoute' => $primaryArea['route'],
        ];
    }

    public function canViewMyBase(User $user): bool
    {
        return $this->hasLinkedBaseChurch($user)
            && $this->portalBaseCapabilityService->canViewBaseOverview($user);
    }

    public function canViewServing(User $user): bool
    {
        return $user->hasRole('Teacher') || $user->hasRole('Mentor');
    }

    public function canViewBaseEvents(User $user): bool
    {
        return $this->hasLinkedBaseChurch($user)
            && $this->portalBaseCapabilityService->canViewBaseOverview($user);
    }

    public function canViewBaseInventory(User $user): bool
    {
        return $this->hasLinkedBaseChurch($user)
            && $this->portalBaseCapabilityService->canViewBaseInventory($user);
    }

    /**
     * @param  array<string, bool>  $portalCapabilities
     * @return array<int, array{key: string, label: string, route: string}>
     */
    public function eventTabs(Training $training, array $portalCapabilities): array
    {
        return collect($this->eventAreas($training, $portalCapabilities))
            ->filter(fn (array $area): bool => $area['visible'])
            ->map(fn (array $area): array => [
                'key' => $area['key'],
                'label' => $area['label'],
                'route' => $area['route'],
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array<string, bool>  $portalCapabilities
     * @return array<int, array{key: string, label: string, description: string, route: ?string, available: bool, tone: string}>
     */
    public function eventAreaCards(Training $training, array $portalCapabilities): array
    {
        return collect($this->eventAreas($training, $portalCapabilities))
            ->filter(fn (array $area): bool => $area['visible'])
            ->map(fn (array $area): array => [
                'key' => $area['key'],
                'label' => $area['label'],
                'description' => $area['description'],
                'route' => $area['route'],
                'available' => $area['available'],
                'tone' => $area['tone'],
            ])
            ->values()
            ->all();
    }

    private function hasLinkedBaseChurch(User $user): bool
    {
        return (int) $user->church_id !== 0;
    }

    /**
     * @param  array<string, bool>  $portalCapabilities
     * @return array<int, array{
     *     key: string,
     *     label: string,
     *     description: string,
     *     route: string,
     *     visible: bool,
     *     available: bool,
     *     tone: string
     * }>
     */
    private function eventAreas(Training $training, array $portalCapabilities): array
    {
        $canViewOverview = (bool) ($portalCapabilities['viewBaseOverview'] ?? false);
        $canManageRegistrations = (bool) ($portalCapabilities['manageTrainingRegistrations'] ?? false);
        $canManageSchedule = (bool) ($portalCapabilities['manageEventSchedule'] ?? false);
        $canViewMaterials = (bool) ($portalCapabilities['viewEventMaterials'] ?? false);
        $canManageMentors = (bool) ($portalCapabilities['manageMentors'] ?? false);
        $canViewServedTrainings = (bool) ($portalCapabilities['viewServedTrainings'] ?? false);
        $canSubmitReports = (bool) (($portalCapabilities['submitChurchEventReport'] ?? false) || ($portalCapabilities['submitTeacherEventReport'] ?? false));

        return [
            [
                'key' => 'show',
                'label' => 'Visao geral',
                'description' => 'Panorama do evento sediado e leitura compartilhada da base.',
                'route' => route('app.portal.base.trainings.show', $training),
                'visible' => $canViewOverview,
                'available' => $canViewOverview,
                'tone' => 'sky',
            ],
            [
                'key' => 'registrations',
                'label' => 'Inscricoes locais',
                'description' => 'Participantes, comprovantes e situacoes que exigem cuidado local.',
                'route' => route('app.portal.base.trainings.registrations', $training),
                'visible' => $canManageRegistrations,
                'available' => $canManageRegistrations,
                'tone' => 'emerald',
            ],
            [
                'key' => 'preparation',
                'label' => 'Preparacao local',
                'description' => 'Checklist de pronto atendimento da igreja anfitria.',
                'route' => route('app.portal.base.trainings.preparation', $training),
                'visible' => $canViewOverview,
                'available' => $canViewOverview,
                'tone' => 'amber',
            ],
            [
                'key' => 'schedule',
                'label' => 'Programacao',
                'description' => 'Agenda do evento e ajustes necessarios para a operacao local.',
                'route' => route('app.portal.base.trainings.schedule', $training),
                'visible' => $canViewOverview,
                'available' => $canManageSchedule,
                'tone' => 'violet',
            ],
            [
                'key' => 'materials',
                'label' => 'Materiais de apoio',
                'description' => 'Acervo, kits e leitura operacional dos recursos da base.',
                'route' => route('app.portal.base.trainings.materials', $training),
                'visible' => $canViewMaterials,
                'available' => $canViewMaterials,
                'tone' => 'slate',
            ],
            [
                'key' => 'statistics',
                'label' => 'Estatisticas/STP',
                'description' => 'Equipes, sessoes e saidas praticas do treinamento.',
                'route' => route('app.portal.base.trainings.statistics', $training),
                'visible' => $canManageMentors,
                'available' => $canManageMentors,
                'tone' => 'sky',
            ],
            [
                'key' => 'stp',
                'label' => 'Abordagens',
                'description' => 'Quadro detalhado das abordagens e visitas do STP.',
                'route' => route('app.portal.base.trainings.stp.approaches', $training),
                'visible' => $canViewServedTrainings && $canManageMentors,
                'available' => $canViewServedTrainings && $canManageMentors,
                'tone' => 'emerald',
            ],
            [
                'key' => 'reports',
                'label' => 'Relatorios do evento',
                'description' => 'Rascunho, envio e status do relatorio da igreja-base e do professor no mesmo contexto do evento.',
                'route' => route('app.portal.base.trainings.reports', $training),
                'visible' => $canViewOverview,
                'available' => $canViewOverview || $canSubmitReports,
                'tone' => 'neutral',
            ],
        ];
    }
}
