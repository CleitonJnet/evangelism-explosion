<?php

namespace App\Services\Metrics;

use App\Models\StpApproach;
use App\Models\StpSession;
use App\Models\StpTeam;
use App\Models\Training;
use App\Services\Stp\StpStatisticsService;

class TrainingDiscipleshipMetricsService
{
    public function __construct(private StpStatisticsService $statisticsService) {}

    /**
     * @param  iterable<int, StpApproach>  $approaches
     * @return array{
     *     people_in_follow_up: int,
     *     started: int,
     *     completed: int,
     *     local_church_referrals: int,
     *     pending_follow_ups: int,
     *     next_steps_registered: int,
     *     sessions_planned: int,
     *     sessions_completed: int
     * }
     */
    public function summarizeParallelTrack(iterable $approaches): array
    {
        $summary = [
            'people_in_follow_up' => 0,
            'started' => 0,
            'completed' => 0,
            'local_church_referrals' => 0,
            'pending_follow_ups' => 0,
            'next_steps_registered' => 0,
            'sessions_planned' => 0,
            'sessions_completed' => 0,
        ];

        foreach ($approaches as $approach) {
            $payload = $approach->discipleshipPayload();

            if (! $approach->hasDiscipleshipTrack()) {
                continue;
            }

            if (in_array($payload['status'], ['pending', 'in_progress'], true) || $payload['completed_at'] === null) {
                $summary['people_in_follow_up']++;
            }

            if ($payload['started_at'] !== null) {
                $summary['started']++;
            }

            if ($payload['completed_at'] !== null || $payload['status'] === 'completed') {
                $summary['completed']++;
            }

            if ($payload['local_church_referral_at'] !== null) {
                $summary['local_church_referrals']++;
            }

            if ($payload['follow_up_pending']) {
                $summary['pending_follow_ups']++;
            }

            if ($payload['next_step'] !== null || $payload['next_step_registered_at'] !== null) {
                $summary['next_steps_registered']++;
            }

            $summary['sessions_planned'] += (int) $payload['sessions_planned'];
            $summary['sessions_completed'] += (int) $payload['sessions_completed'];
        }

        return $summary;
    }

    /**
     * @return array{
     *     mentorsCount: int,
     *     isLeadershipExecutionTraining: bool,
     *     sessions: array<int, array{id: int, label: string}>,
     *     activeSessionId: ?int,
     *     pendingStudents: array<int, array{student_id: int, name: string, participated: int, missing: int}>,
     *     canCreateSession: bool,
     *     createSessionBlockedReason: ?string,
     *     teams: array<int, array{
     *         id: int,
     *         name: string,
     *         mentor: array{id: int, name: string},
     *         students: array<int, array{id: int, name: string}>,
     *         visitant: int,
     *         questionnaire: int,
     *         indication: int,
     *         lifeway: int,
     *         totExplained: int,
     *         totPeople: int,
     *         totDecision: int,
     *         totInteresting: int,
     *         totReject: int,
     *         totChristian: int,
     *         meansGrowth: int,
     *         folowship: int
     *     }>,
     *     columnTotals: array<int, int>,
     *     canRandomizeTeams: bool
     * }
     */
    public function buildTrainingBoard(Training $training, ?int $activeSessionId = null): array
    {
        $training = Training::query()
            ->with([
                'course',
                'stpSessions' => fn ($query) => $query->orderBy('sequence')->orderBy('id'),
            ])
            ->withCount('mentors')
            ->findOrFail($training->id);

        $sessions = $training->stpSessions
            ->map(fn (StpSession $session): array => [
                'id' => $session->id,
                'label' => $this->formatSessionLabel($session),
            ])
            ->values()
            ->all();

        $resolvedActiveSessionId = $this->resolveActiveSessionId($training, $activeSessionId);
        $createSessionState = $this->buildCreateSessionState($training);
        $teamBoard = $this->buildActiveSessionTeamBoard($training, $resolvedActiveSessionId);

        return [
            'mentorsCount' => (int) $training->mentors_count,
            'isLeadershipExecutionTraining' => (int) ($training->course?->execution ?? -1) === 0,
            'sessions' => $sessions,
            'activeSessionId' => $resolvedActiveSessionId,
            'pendingStudents' => $this->statisticsService->studentsBelowMinimum($training),
            'canCreateSession' => $createSessionState['canCreateSession'],
            'createSessionBlockedReason' => $createSessionState['createSessionBlockedReason'],
            'teams' => $teamBoard['teams'],
            'columnTotals' => $teamBoard['columnTotals'],
            'canRandomizeTeams' => $teamBoard['canRandomizeTeams'],
        ];
    }

    public function formatSessionLabel(StpSession $session): string
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

    /**
     * @return array{canCreateSession: bool, createSessionBlockedReason: ?string}
     */
    public function buildCreateSessionState(Training $training): array
    {
        $training = Training::query()
            ->withCount(['mentors', 'students'])
            ->findOrFail($training->id);

        if ($training->mentors_count < 1 || $training->students_count < 1) {
            return [
                'canCreateSession' => false,
                'createSessionBlockedReason' => 'Cadastre ao menos 1 mentor e 1 aluno no treinamento antes de criar sessões STP.',
            ];
        }

        $lastSession = StpSession::query()
            ->with(['teams.students'])
            ->where('training_id', $training->id)
            ->orderByDesc('sequence')
            ->orderByDesc('id')
            ->first();

        if (! $lastSession) {
            return [
                'canCreateSession' => true,
                'createSessionBlockedReason' => null,
            ];
        }

        $hasMentors = $lastSession->teams->isNotEmpty();
        $hasStudents = $lastSession->teams->sum(fn (StpTeam $team): int => $team->students->count()) > 0;

        if (! $hasMentors || ! $hasStudents) {
            return [
                'canCreateSession' => false,
                'createSessionBlockedReason' => 'A sessão anterior precisa ter mentores e alunos distribuídos antes de criar uma nova.',
            ];
        }

        return [
            'canCreateSession' => true,
            'createSessionBlockedReason' => null,
        ];
    }

    /**
     * @return array{
     *     teams: array<int, array{
     *         id: int,
     *         name: string,
     *         mentor: array{id: int, name: string},
     *         students: array<int, array{id: int, name: string}>,
     *         visitant: int,
     *         questionnaire: int,
     *         indication: int,
     *         lifeway: int,
     *         totExplained: int,
     *         totPeople: int,
     *         totDecision: int,
     *         totInteresting: int,
     *         totReject: int,
     *         totChristian: int,
     *         meansGrowth: int,
     *         folowship: int
     *     }>,
     *     columnTotals: array<int, int>,
     *     canRandomizeTeams: bool
     * }
     */
    public function buildActiveSessionTeamBoard(Training $training, ?int $activeSessionId): array
    {
        if ($activeSessionId === null) {
            return [
                'teams' => [],
                'columnTotals' => array_fill(0, 12, 0),
                'canRandomizeTeams' => false,
            ];
        }

        $session = StpSession::query()
            ->where('training_id', $training->id)
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
            ->find($activeSessionId);

        if (! $session) {
            return [
                'teams' => [],
                'columnTotals' => array_fill(0, 12, 0),
                'canRandomizeTeams' => false,
            ];
        }

        $statsByTeam = collect($this->statisticsService->teamStats($session))->keyBy('team_id');

        $teams = $session->teams
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

        return [
            'teams' => $teams,
            'columnTotals' => $this->computeColumnTotals($teams),
            'canRandomizeTeams' => ! StpApproach::query()
                ->where('stp_session_id', $session->id)
                ->whereNotNull('stp_team_id')
                ->exists(),
        ];
    }

    private function resolveActiveSessionId(Training $training, ?int $activeSessionId): ?int
    {
        if ($activeSessionId === null) {
            return $training->stpSessions->last()?->id;
        }

        $exists = $training->stpSessions
            ->contains(fn (StpSession $session): bool => $session->id === $activeSessionId);

        if ($exists) {
            return $activeSessionId;
        }

        return $training->stpSessions->last()?->id;
    }

    /**
     * @param  array<int, array<string, mixed>>  $teams
     * @return array<int, int>
     */
    private function computeColumnTotals(array $teams): array
    {
        $totals = array_fill(0, 12, 0);

        foreach ($teams as $team) {
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

        return $totals;
    }
}
