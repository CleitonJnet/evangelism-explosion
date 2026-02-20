<?php

namespace App\Livewire\Pages\App\Teacher\Training;

use App\Models\StpSession;
use App\Models\StpTeam;
use App\Models\Training;
use App\Services\Stp\StpSessionService;
use App\Services\Stp\StpStatisticsService;
use App\Services\Stp\StpTeamFormationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class Statistics extends Component
{
    use AuthorizesRequests;

    public Training $training;

    public ?int $activeSessionId = null;

    /**
     * @var array<int, array{id: int, label: string}>
     */
    public array $sessions = [];

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
     *     meansGrowth: int,
     *     folowship: int
     * }>
     */
    public array $teams = [];

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

    /**
     * @var array<int, array{student_id: int, name: string, participated: int, missing: int}>
     */
    public array $pendingStudents = [];

    public bool $canCreateSession = true;

    public ?string $createSessionBlockedReason = null;

    public int $mentorsCount = 0;

    public function mount(Training $training): void
    {
        $this->authorize('view', $training);
        $this->training = $training;

        $this->refreshSessionsAndTeams();
    }

    /**
     * @param  array{label?: ?string, starts_at?: mixed, ends_at?: mixed, status?: ?string}  $data
     */
    public function createSession(array $data = []): void
    {
        $this->authorize('view', $this->training);
        $this->refreshCreateSessionState();

        if (! $this->canCreateSession) {
            $this->addError('sessionCreation', $this->createSessionBlockedReason ?? 'Não foi possível criar a sessão STP.');

            return;
        }

        $session = app(StpSessionService::class)->createNextSession($this->training, $data);
        $this->activeSessionId = $session->id;
        $this->resetErrorBag('sessionCreation');

        $this->refreshSessionsAndTeams();
    }

    public function removeSession(int $sessionId): void
    {
        $this->authorize('view', $this->training);

        $session = StpSession::query()
            ->where('training_id', $this->training->id)
            ->find($sessionId);

        if (! $session) {
            return;
        }

        $session->delete();

        if ($this->activeSessionId === $sessionId) {
            $this->activeSessionId = null;
        }

        $this->refreshSessionsAndTeams();
    }

    public function formTeams(): void
    {
        $this->authorize('view', $this->training);

        $session = $this->activeSession();

        if (! $session) {
            return;
        }

        try {
            app(StpTeamFormationService::class)->formTeams($session);
            $this->resetErrorBag('teamFormation');
        } catch (\RuntimeException $exception) {
            $this->addError('teamFormation', $exception->getMessage());
        }

        $this->refreshSessionsAndTeams();
    }

    public function moveStudent(int $studentId, int $fromTeamId, int $toTeamId, ?int $afterStudentId = null): void
    {
        $this->authorize('view', $this->training);

        $session = $this->activeSession();

        if (! $session) {
            return;
        }

        DB::transaction(function () use ($session, $studentId, $fromTeamId, $toTeamId, $afterStudentId): void {
            $teamIds = StpTeam::query()
                ->where('stp_session_id', $session->id)
                ->pluck('id')
                ->map(fn (mixed $id): int => (int) $id)
                ->all();

            if (! in_array($fromTeamId, $teamIds, true) || ! in_array($toTeamId, $teamIds, true)) {
                return;
            }

            DB::table('stp_team_students')
                ->whereIn('stp_team_id', $teamIds)
                ->where('user_id', $studentId)
                ->delete();

            $destinationIds = DB::table('stp_team_students')
                ->where('stp_team_id', $toTeamId)
                ->orderBy('position')
                ->orderBy('id')
                ->pluck('user_id')
                ->map(fn (mixed $id): int => (int) $id)
                ->all();

            $destinationIds = array_values(array_filter(
                $destinationIds,
                fn (int $id): bool => $id !== $studentId,
            ));

            $insertIndex = 0;

            if ($afterStudentId !== null) {
                $afterIndex = array_search($afterStudentId, $destinationIds, true);
                $insertIndex = $afterIndex === false ? count($destinationIds) : ((int) $afterIndex + 1);
            }

            array_splice($destinationIds, $insertIndex, 0, [$studentId]);

            $now = now();

            foreach ($destinationIds as $position => $id) {
                DB::table('stp_team_students')->updateOrInsert(
                    [
                        'stp_team_id' => $toTeamId,
                        'user_id' => $id,
                    ],
                    [
                        'position' => $position,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ],
                );
            }

            $this->normalizeTeamStudentPositions($fromTeamId);

            if ($fromTeamId !== $toTeamId) {
                $this->normalizeTeamStudentPositions($toTeamId);
            }
        });

        $this->loadTeamsAndStats();
    }

    public function swapMentor(int $mentorId, int $fromTeamId, int $toTeamId): void
    {
        $this->authorize('view', $this->training);

        $session = $this->activeSession();

        if (! $session || $fromTeamId === $toTeamId) {
            return;
        }

        DB::transaction(function () use ($session, $mentorId, $fromTeamId, $toTeamId): void {
            $fromTeam = StpTeam::query()
                ->where('stp_session_id', $session->id)
                ->find($fromTeamId);
            $toTeam = StpTeam::query()
                ->where('stp_session_id', $session->id)
                ->find($toTeamId);

            if (! $fromTeam || ! $toTeam) {
                return;
            }

            if ((int) $fromTeam->mentor_user_id !== $mentorId) {
                return;
            }

            $sourceMentorId = (int) $fromTeam->mentor_user_id;
            $targetMentorId = (int) $toTeam->mentor_user_id;

            $fromTeam->mentor_user_id = $targetMentorId;
            $toTeam->mentor_user_id = $sourceMentorId;

            $fromTeam->save();
            $toTeam->save();
        });

        $this->loadTeamsAndStats();
    }

    public function selectSession(int|string $sessionId): void
    {
        $sessionId = (int) $sessionId;

        $exists = collect($this->sessions)
            ->contains(fn (array $session): bool => $session['id'] === $sessionId);

        if (! $exists) {
            return;
        }

        $this->activeSessionId = $sessionId;
        $this->loadTeamsAndStats();
    }

    public function render(): View
    {
        return view('livewire.pages.app.teacher.training.statistics');
    }

    private function refreshSessionsAndTeams(): void
    {
        $this->training = Training::query()
            ->with([
                'course',
                'stpSessions' => fn ($query) => $query->orderBy('sequence')->orderBy('id'),
            ])
            ->withCount('mentors')
            ->findOrFail($this->training->id);

        $this->mentorsCount = (int) $this->training->mentors_count;

        $this->sessions = $this->training->stpSessions
            ->map(function (StpSession $session): array {
                return [
                    'id' => $session->id,
                    'label' => $this->formatSessionLabel($session),
                ];
            })
            ->values()
            ->all();

        if ($this->activeSessionId === null) {
            $this->activeSessionId = $this->training->stpSessions->last()?->id;
        } else {
            $activeSessionExists = $this->training->stpSessions
                ->contains(fn (StpSession $session): bool => $session->id === $this->activeSessionId);

            if (! $activeSessionExists) {
                $this->activeSessionId = $this->training->stpSessions->last()?->id;
            }
        }

        $this->pendingStudents = app(StpStatisticsService::class)->studentsBelowMinimum($this->training);
        $this->refreshCreateSessionState();

        $this->loadTeamsAndStats();
    }

    private function refreshCreateSessionState(): void
    {
        $training = Training::query()
            ->withCount(['mentors', 'students'])
            ->findOrFail($this->training->id);

        if ($training->mentors_count < 1 || $training->students_count < 1) {
            $this->canCreateSession = false;
            $this->createSessionBlockedReason = 'Cadastre ao menos 1 mentor e 1 aluno no treinamento antes de criar sessões STP.';

            return;
        }

        $lastSession = StpSession::query()
            ->with(['teams.students'])
            ->where('training_id', $this->training->id)
            ->orderByDesc('sequence')
            ->orderByDesc('id')
            ->first();

        if (! $lastSession) {
            $this->canCreateSession = true;
            $this->createSessionBlockedReason = null;

            return;
        }

        $hasMentors = $lastSession->teams->isNotEmpty();
        $hasStudents = $lastSession->teams->sum(fn (StpTeam $team): int => $team->students->count()) > 0;

        if (! $hasMentors || ! $hasStudents) {
            $this->canCreateSession = false;
            $this->createSessionBlockedReason = 'A sessão anterior precisa ter mentores e alunos distribuídos antes de criar uma nova.';

            return;
        }

        $this->canCreateSession = true;
        $this->createSessionBlockedReason = null;
    }

    private function loadTeamsAndStats(): void
    {
        $session = $this->activeSession();

        if (! $session) {
            $this->teams = [];
            $this->columnTotals = array_fill(0, 12, 0);

            return;
        }

        $session = StpSession::query()
            ->with([
                'teams' => fn ($query) => $query
                    ->with([
                        'mentor',
                        'students' => fn ($studentsQuery) => $studentsQuery
                            ->orderBy('stp_team_students.position')
                            ->orderBy('name'),
                    ])
                    ->orderBy('position')
                    ->orderBy('id'),
            ])
            ->findOrFail($session->id);

        $statsByTeam = collect(app(StpStatisticsService::class)->teamStats($session))
            ->keyBy('team_id');

        $this->teams = $session->teams
            ->map(function (StpTeam $team) use ($statsByTeam): array {
                /** @var array<string, mixed> $stats */
                $stats = $statsByTeam->get($team->id, [
                    'visitant' => 0,
                    'questionnaire' => 0,
                    'indication' => 0,
                    'lifeway' => 0,
                    'totExplained' => 0,
                    'totPeople' => 0,
                    'totDecision' => 0,
                    'totInteresting' => 0,
                    'totReject' => 0,
                    'totChristian' => 0,
                    'meansGrowth' => 0,
                    'folowship' => 0,
                ]);

                return [
                    'id' => $team->id,
                    'name' => $team->name,
                    'mentor' => [
                        'id' => (int) $team->mentor_user_id,
                        'name' => $team->mentor?->name ?? 'Mentor não definido',
                    ],
                    'students' => $team->students
                        ->map(fn ($student): array => [
                            'id' => $student->id,
                            'name' => $student->name,
                        ])
                        ->values()
                        ->all(),
                    'visitant' => (int) ($stats['visitant'] ?? 0),
                    'questionnaire' => (int) ($stats['questionnaire'] ?? 0),
                    'indication' => (int) ($stats['indication'] ?? 0),
                    'lifeway' => (int) ($stats['lifeway'] ?? 0),
                    'totExplained' => (int) ($stats['totExplained'] ?? 0),
                    'totPeople' => (int) ($stats['totPeople'] ?? 0),
                    'totDecision' => (int) ($stats['totDecision'] ?? 0),
                    'totInteresting' => (int) ($stats['totInteresting'] ?? 0),
                    'totReject' => (int) ($stats['totReject'] ?? 0),
                    'totChristian' => (int) ($stats['totChristian'] ?? 0),
                    'meansGrowth' => (int) ($stats['meansGrowth'] ?? 0),
                    'folowship' => (int) ($stats['folowship'] ?? 0),
                ];
            })
            ->values()
            ->all();

        $this->recomputeTotals();
    }

    private function recomputeTotals(): void
    {
        $totals = array_fill(0, 12, 0);

        foreach ($this->teams as $team) {
            $values = [
                $team['visitant'],
                $team['questionnaire'],
                $team['indication'],
                $team['lifeway'],
                $team['totExplained'],
                $team['totPeople'],
                $team['totDecision'],
                $team['totInteresting'],
                $team['totReject'],
                $team['totChristian'],
                $team['meansGrowth'],
                $team['folowship'],
            ];

            foreach ($values as $index => $value) {
                $totals[$index] += (int) $value;
            }
        }

        $this->columnTotals = $totals;
    }

    private function activeSession(): ?StpSession
    {
        if ($this->activeSessionId === null) {
            return null;
        }

        return StpSession::query()
            ->where('training_id', $this->training->id)
            ->find($this->activeSessionId);
    }

    private function formatSessionLabel(StpSession $session): string
    {
        $base = sprintf('Sessão %d', (int) $session->sequence);

        if ($session->label) {
            return $base.': '.$session->label;
        }

        if ($session->starts_at || $session->ends_at) {
            $startsAt = $session->starts_at?->format('d/m H:i');
            $endsAt = $session->ends_at?->format('d/m H:i');
            $timeLabel = trim(($startsAt ?? '').' - '.($endsAt ?? ''), ' -');

            if ($timeLabel !== '') {
                return $base.': '.$timeLabel;
            }
        }

        return $base;
    }

    private function normalizeTeamStudentPositions(int $teamId): void
    {
        $rows = DB::table('stp_team_students')
            ->where('stp_team_id', $teamId)
            ->orderBy('position')
            ->orderBy('id')
            ->get(['id', 'position']);

        foreach ($rows as $position => $row) {
            if ((int) $row->position === $position) {
                continue;
            }

            DB::table('stp_team_students')
                ->where('id', $row->id)
                ->update([
                    'position' => $position,
                    'updated_at' => now(),
                ]);
        }
    }
}
