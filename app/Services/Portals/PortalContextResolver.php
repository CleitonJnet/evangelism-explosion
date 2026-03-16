<?php

namespace App\Services\Portals;

use App\Models\User;
use App\Support\Portals\Data\PortalContextData;
use App\Support\Portals\Enums\Portal;

class PortalContextResolver
{
    public function resolve(User $user, Portal $portal): PortalContextData
    {
        return match ($portal) {
            Portal::Base => new PortalContextData(
                portal: $portal,
                headline: 'Portal Base e Treinamentos',
                description: 'Operacao local da igreja-base, eventos sediados e treinamentos em que voce serve, tudo em uma mesma experiencia.',
                roleHints: $this->roleHints($user, ['Director', 'Teacher', 'Facilitator', 'Mentor', 'FieldWorker']),
                focusAreas: ['Minha Base', 'Treinamentos em que Sirvo', 'Eventos da Base'],
            ),
            Portal::Staff => new PortalContextData(
                portal: $portal,
                headline: 'Portal Staff / Governanca',
                description: 'Camada inicial para governanca, staff nacional e coordenacao institucional.',
                roleHints: $this->roleHints($user, ['Board', 'Director', 'FieldWorker']),
                focusAreas: ['Governanca', 'Gestao institucional', 'Coordenacao executiva'],
            ),
            Portal::Student => new PortalContextData(
                portal: $portal,
                headline: 'Portal Aluno',
                description: 'Camada inicial para jornada do aluno, acompanhamento e consumo de conteudo.',
                roleHints: $this->roleHints($user, ['Student']),
                focusAreas: ['Minha jornada', 'Treinamentos inscritos', 'Materiais e acompanhamento'],
            ),
        };
    }

    /**
     * @param  array<int, string>  $allowedRoles
     * @return array<int, string>
     */
    protected function roleHints(User $user, array $allowedRoles): array
    {
        return $user->roles()
            ->whereIn('name', $allowedRoles)
            ->pluck('name')
            ->values()
            ->all();
    }
}
