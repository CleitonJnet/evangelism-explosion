<?php

namespace App\Services\Portals;

use App\Models\User;

class StaffCouncilService
{
    /**
     * @return array<string, mixed>
     */
    public function build(User $user): array
    {
        return [
            'summary' => [
                'tracks_count' => 3,
                'placeholders_count' => 7,
                'can_curate' => $user->hasRole('Board') || $user->hasRole('Director'),
            ],
            'guiding_principles' => [
                'Separar governanca institucional da operacao de eventos e da fila de evidencias.',
                'Preparar o espaco para memoria documental, pauta recorrente e deliberacao registrada.',
                'Permitir evolucao gradual sem reescrever a navegacao do portal Staff depois.',
            ],
            'tracks' => [
                [
                    'title' => 'Documentos institucionais',
                    'description' => 'Espaco inicial para estatuto, regimentos, politicas internas, pareceres e memoria documental do Conselho Nacional.',
                    'status' => 'Estrutura pronta para catalogacao',
                    'items' => [
                        'Biblioteca documental por categoria',
                        'Versoes, vigencias e responsaveis',
                    ],
                ],
                [
                    'title' => 'Pautas e agendas',
                    'description' => 'Area reservada para pautas futuras, reunioes ordinarias, temas recorrentes e checkpoints de acompanhamento.',
                    'status' => 'Trilha inicial de agenda',
                    'items' => [
                        'Calendario de reunioes e pautas',
                        'Preparacao de reuniao e materiais de apoio',
                    ],
                ],
                [
                    'title' => 'Deliberacoes e acompanhamentos',
                    'description' => 'Base para registrar decisoes, encaminhamentos, status de execucao e historico de acompanhamento institucional.',
                    'status' => 'Pronto para workflow futuro',
                    'items' => [
                        'Registro de decisoes por reuniao',
                        'Encaminhamentos, prazos e responsaveis',
                        'Historico de status e revalidacao',
                    ],
                ],
            ],
            'next_steps' => [
                'Definir tipologia oficial dos documentos e permissoes de consulta.',
                'Estruturar a agenda fixa do Conselho com cadencia e papeis.',
                'Modelar deliberacoes com status, responsavel e prazo.',
            ],
        ];
    }
}
