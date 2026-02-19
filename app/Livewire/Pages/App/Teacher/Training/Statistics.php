<?php

namespace App\Livewire\Pages\App\Teacher\Training;

use App\Models\Training;
use Carbon\Carbon;
use Illuminate\View\View;
use Livewire\Component;

class Statistics extends Component
{
    public Training $training;

    /**
     * @var array<int, array{
     *     id: int,
     *     name: string,
     *     mentor: array{id: int, name: string},
     *     students: array<int, array{id: int, name: string}>,
     *     visitant: int,
     *     questionnaire: int,
     *     indication: int,
     *     lifeway: int,
     *     totExplained: int,
     *     totPeople: int,
     *     totDecision: int,
     *     totInteresting: int,
     *     totReject: int,
     *     totChristian: int,
     *     meansGrowth: bool,
     *     folowship: int
     * }>
     */
    public array $approaches = [];

    /**
     * @var array<int, string>
     */
    public array $sessions = [];

    /**
     * @var array<int, string>
     */
    public array $typeContactLabels = [
        'Visitante da Igreja',
        'Questionario',
        'Indicacao',
        'Estilo de Vida',
    ];

    /**
     * @var array<int, string>
     */
    public array $gospelLabels = [
        'Quantas vezes?',
        'Para quantas pessoas?',
    ];

    /**
     * @var array<int, string>
     */
    public array $resultLabels = [
        'Decisao',
        'Sem decisao/ interessado',
        'Rejeicao',
        'Para seguranca/Ja e crente',
    ];

    /**
     * @var array<int, string>
     */
    public array $followUpLabels = [
        'Acomp. Esp. (meios de cresc.)',
        'Visita agendada (7 dias apos)',
    ];

    /**
     * @var array<int, int>
     */
    public array $columnTotals = [];

    public function mount(Training $training): void
    {
        $this->training = $training->loadMissing(['eventDates']);
        $this->approaches = $this->buildApproaches();
        $this->sessions = $this->buildSessions();
        $this->columnTotals = $this->calculateColumnTotals($this->approaches);
    }

    public function moveStudent(int $studentId, int $fromApproachId, int $toApproachId, ?int $afterStudentId = null): void
    {
        $fromApproachIndex = $this->findApproachIndexById($fromApproachId);
        $toApproachIndex = $this->findApproachIndexById($toApproachId);

        if ($fromApproachIndex === null || $toApproachIndex === null) {
            return;
        }

        $studentIndex = collect($this->approaches[$fromApproachIndex]['students'])
            ->search(fn (array $student): bool => $student['id'] === $studentId);

        if ($studentIndex === false) {
            return;
        }

        $student = $this->approaches[$fromApproachIndex]['students'][$studentIndex];
        array_splice($this->approaches[$fromApproachIndex]['students'], (int) $studentIndex, 1);

        if ($afterStudentId === null) {
            array_unshift($this->approaches[$toApproachIndex]['students'], $student);
        } else {
            $afterIndex = collect($this->approaches[$toApproachIndex]['students'])
                ->search(fn (array $item): bool => $item['id'] === $afterStudentId);

            if ($afterIndex === false) {
                $this->approaches[$toApproachIndex]['students'][] = $student;
            } else {
                array_splice($this->approaches[$toApproachIndex]['students'], (int) $afterIndex + 1, 0, [$student]);
            }
        }

        $this->sortStudentsByName($fromApproachIndex);

        if ($fromApproachIndex !== $toApproachIndex) {
            $this->sortStudentsByName($toApproachIndex);
        }
    }

    public function swapMentor(int $mentorId, int $fromApproachId, int $toApproachId): void
    {
        $fromApproachIndex = $this->findApproachIndexById($fromApproachId);
        $toApproachIndex = $this->findApproachIndexById($toApproachId);

        if ($fromApproachIndex === null || $toApproachIndex === null) {
            return;
        }

        if ($fromApproachIndex === $toApproachIndex) {
            return;
        }

        if ($this->approaches[$fromApproachIndex]['mentor']['id'] !== $mentorId) {
            return;
        }

        $sourceMentor = $this->approaches[$fromApproachIndex]['mentor'];
        $targetMentor = $this->approaches[$toApproachIndex]['mentor'];

        $this->approaches[$fromApproachIndex]['mentor'] = $targetMentor;
        $this->approaches[$toApproachIndex]['mentor'] = $sourceMentor;
    }

    public function render(): View
    {
        return view('livewire.pages.app.teacher.training.statistics');
    }

    /**
     * @return array<int, array{
     *     id: int,
     *     name: string,
     *     mentor: array{id: int, name: string},
     *     students: array<int, array{id: int, name: string}>,
     *     visitant: int,
     *     questionnaire: int,
     *     indication: int,
     *     lifeway: int,
     *     totExplained: int,
     *     totPeople: int,
     *     totDecision: int,
     *     totInteresting: int,
     *     totReject: int,
     *     totChristian: int,
     *     meansGrowth: bool,
     *     folowship: int
     * }>
     */
    private function buildApproaches(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Fernando Pedrosa',
                'mentor' => ['id' => 1001, 'name' => 'Dc. Antonio Maia'],
                'students' => [
                    ['id' => 2001, 'name' => 'Pb. Gabriel Ferreira'],
                    ['id' => 2002, 'name' => 'Maria Jose'],
                ],
                'visitant' => 0,
                'questionnaire' => 3,
                'indication' => 1,
                'lifeway' => 0,
                'totExplained' => 4,
                'totPeople' => 10,
                'totDecision' => 5,
                'totInteresting' => 3,
                'totReject' => 1,
                'totChristian' => 1,
                'meansGrowth' => true,
                'folowship' => 9,
            ],
            [
                'id' => 2,
                'name' => 'Joao Batista',
                'mentor' => ['id' => 1002, 'name' => 'Pr. Cleverson Pereira Rodrigues'],
                'students' => [
                    ['id' => 2003, 'name' => 'Ana Paula Souza'],
                    ['id' => 2004, 'name' => 'Carlos Eduardo Lima'],
                ],
                'visitant' => 1,
                'questionnaire' => 1,
                'indication' => 1,
                'lifeway' => 0,
                'totExplained' => 4,
                'totPeople' => 10,
                'totDecision' => 5,
                'totInteresting' => 3,
                'totReject' => 1,
                'totChristian' => 1,
                'meansGrowth' => true,
                'folowship' => 9,
            ],
            [
                'id' => 3,
                'name' => 'Fernando Galego',
                'mentor' => ['id' => 1003, 'name' => 'Mariana Chagas'],
                'students' => [
                    ['id' => 2005, 'name' => 'Joao Pedro Martins'],
                    ['id' => 2006, 'name' => 'Juliana Alves'],
                ],
                'visitant' => 0,
                'questionnaire' => 3,
                'indication' => 0,
                'lifeway' => 2,
                'totExplained' => 4,
                'totPeople' => 10,
                'totDecision' => 5,
                'totInteresting' => 3,
                'totReject' => 1,
                'totChristian' => 1,
                'meansGrowth' => true,
                'folowship' => 9,
            ],
            [
                'id' => 4,
                'name' => 'Thomas Ropkings',
                'mentor' => ['id' => 1004, 'name' => 'Laender Souza'],
                'students' => [
                    ['id' => 2007, 'name' => 'Rafael Henrique Silva'],
                    ['id' => 2008, 'name' => 'Camila Rodrigues'],
                ],
                'visitant' => 0,
                'questionnaire' => 3,
                'indication' => 1,
                'lifeway' => 0,
                'totExplained' => 4,
                'totPeople' => 10,
                'totDecision' => 5,
                'totInteresting' => 3,
                'totReject' => 1,
                'totChristian' => 1,
                'meansGrowth' => true,
                'folowship' => 9,
            ],
            [
                'id' => 5,
                'name' => 'Willian Tyndale',
                'mentor' => ['id' => 1005, 'name' => 'Marcelo Ponce'],
                'students' => [
                    ['id' => 2009, 'name' => 'Bruno Vieira'],
                    ['id' => 2010, 'name' => 'Patricia Nascimento'],
                ],
                'visitant' => 0,
                'questionnaire' => 3,
                'indication' => 1,
                'lifeway' => 0,
                'totExplained' => 4,
                'totPeople' => 10,
                'totDecision' => 5,
                'totInteresting' => 3,
                'totReject' => 1,
                'totChristian' => 1,
                'meansGrowth' => true,
                'folowship' => 9,
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function buildSessions(): array
    {
        $eventDates = $this->training->eventDates->sortBy('date')->values();

        if ($eventDates->isNotEmpty()) {
            return $eventDates
                ->map(function ($eventDate, int $index): string {
                    $dateLabel = Carbon::parse((string) $eventDate->date)->format('d/m/Y');

                    return sprintf('Sessão %d: %s', $index + 1, $dateLabel);
                })
                ->all();
        }

        return [
            'Sessão 1: 01/01/2026',
            'Sessão 2: 02/01/2026',
            'Sessão 3: 03/01/2026',
        ];
    }

    /**
     * @param  array<int, array{
     *     id: int,
     *     name: string,
     *     mentor: array{id: int, name: string},
     *     students: array<int, array{id: int, name: string}>,
     *     visitant: int,
     *     questionnaire: int,
     *     indication: int,
     *     lifeway: int,
     *     totExplained: int,
     *     totPeople: int,
     *     totDecision: int,
     *     totInteresting: int,
     *     totReject: int,
     *     totChristian: int,
     *     meansGrowth: bool,
     *     folowship: int
     * }>  $approaches
     * @return array<int, int>
     */
    private function calculateColumnTotals(array $approaches): array
    {
        $totals = array_fill(0, 12, 0);

        foreach ($approaches as $approach) {
            $values = [
                $approach['visitant'],
                $approach['questionnaire'],
                $approach['indication'],
                $approach['lifeway'],
                $approach['totExplained'],
                $approach['totPeople'],
                $approach['totDecision'],
                $approach['totInteresting'],
                $approach['totReject'],
                $approach['totChristian'],
                $approach['meansGrowth'] ? 1 : 0,
                $approach['folowship'],
            ];

            foreach ($values as $index => $value) {
                $totals[$index] += $value;
            }
        }

        return $totals;
    }

    private function findApproachIndexById(int $approachId): ?int
    {
        $index = collect($this->approaches)
            ->search(fn (array $approach): bool => $approach['id'] === $approachId);

        return $index === false ? null : (int) $index;
    }

    private function sortStudentsByName(int $approachIndex): void
    {
        usort($this->approaches[$approachIndex]['students'], function (array $left, array $right): int {
            return strcmp(
                mb_strtolower($left['name']),
                mb_strtolower($right['name']),
            );
        });
    }
}
