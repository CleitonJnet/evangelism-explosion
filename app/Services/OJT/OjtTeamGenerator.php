<?php

namespace App\Services\OJT;

use App\Models\OjtSession;
use App\Models\OjtTeam;
use App\Models\OjtTeamTrainee;
use App\Models\OjtTrainingMentor;
use App\Models\Training;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OjtTeamGenerator
{
    public function generate(Training $training): OjtTeamGenerationResult
    {
        return DB::transaction(function () use ($training): OjtTeamGenerationResult {
            $training->loadMissing('course');

            $sessions = OjtSession::query()
                ->where('training_id', $training->id)
                ->with(['teams.trainees'])
                ->orderBy('date')
                ->orderBy('starts_at')
                ->orderBy('id')
                ->get();

            $mentorIds = OjtTrainingMentor::query()
                ->where('training_id', $training->id)
                ->where('status', 'active')
                ->orderBy('mentor_id')
                ->pluck('mentor_id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();

            $traineeIds = $training->students()
                ->distinct()
                ->orderBy('users.id')
                ->pluck('training_user.user_id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();

            $warnings = [];
            $created = collect();

            if ($sessions->isEmpty()) {
                $warnings[] = $this->warning('no_sessions', 'No OJT sessions available to generate teams.', null);

                return new OjtTeamGenerationResult($created, $warnings);
            }

            if (count($traineeIds) < 2) {
                $warnings[] = $this->warning('not_enough_trainees', 'At least two trainees are required to form a team.', null);

                return new OjtTeamGenerationResult($created, $warnings);
            }

            if ($mentorIds === []) {
                $warnings[] = $this->warning('no_mentors', 'No active mentors assigned to this training.', null);

                return new OjtTeamGenerationResult($created, $warnings);
            }

            $traineeTeams = intdiv(count($traineeIds), 2);

            if ($traineeTeams === 0) {
                $warnings[] = $this->warning('not_enough_trainees', 'At least two trainees are required to form a team.', null);

                return new OjtTeamGenerationResult($created, $warnings);
            }

            if (count($mentorIds) < $traineeTeams) {
                $warnings[] = $this->warning(
                    'mentor_shortage',
                    'Not enough mentors to cover all trainee pairs; mentors will be reused.',
                    null,
                );
            }

            $policy = $training->ojtPolicy();

            if ($policy === Training::OJT_POLICY_FIXED) {
                [$created, $warnings] = $this->generateFixed($sessions, $mentorIds, $traineeIds, $traineeTeams, $warnings);

                return new OjtTeamGenerationResult($created, $warnings);
            }

            $pairingCounts = $this->pairingCounts($training);

            foreach ($sessions as $session) {
                if ($session->teams->isNotEmpty()) {
                    $warnings[] = $this->warning('session_has_teams', 'Session already has teams; generation skipped.', $session->id);
                    $this->applyExistingPairsToCounts($session, $pairingCounts);

                    continue;
                }

                $result = $this->buildTeamsForSession($session, $mentorIds, $traineeIds, $traineeTeams, $pairingCounts);
                $created = $created->merge($result['created']);
                $warnings = array_merge($warnings, $result['warnings']);
            }

            return new OjtTeamGenerationResult($created, $warnings);
        });
    }

    /**
     * @param  Collection<int, OjtSession>  $sessions
     * @param  array<int, int>  $mentorIds
     * @param  array<int, int>  $traineeIds
     * @param  array<int, array{type: string, message: string, session_id: int|null}>  $warnings
     * @return array{0: Collection<int, OjtTeam>, 1: array<int, array{type: string, message: string, session_id: int|null}>}
     */
    private function generateFixed(
        Collection $sessions,
        array $mentorIds,
        array $traineeIds,
        int $teamCount,
        array $warnings,
    ): array {
        $created = collect();
        $firstSession = $sessions->first();

        if (! $firstSession) {
            $warnings[] = $this->warning('no_sessions', 'No OJT sessions available to generate teams.', null);

            return [$created, $warnings];
        }

        $template = $this->extractTemplate($firstSession);

        if ($template === []) {
            $pairingCounts = [];
            $result = $this->buildTeamsForSession($firstSession, $mentorIds, $traineeIds, $teamCount, $pairingCounts);
            $created = $created->merge($result['created']);
            $warnings = array_merge($warnings, $result['warnings']);
            $template = $this->extractTemplate($firstSession->fresh(['teams.trainees']));
        }

        if ($template === []) {
            $warnings[] = $this->warning('no_template', 'Unable to build a fixed team template for the first session.', $firstSession->id);

            return [$created, $warnings];
        }

        foreach ($sessions->slice(1) as $session) {
            if ($session->teams->isNotEmpty()) {
                $warnings[] = $this->warning('session_has_teams', 'Session already has teams; generation skipped.', $session->id);

                continue;
            }

            $teamNumber = 1;

            foreach ($template as $definition) {
                $team = OjtTeam::query()->create([
                    'ojt_session_id' => $session->id,
                    'mentor_id' => $definition['mentor_id'],
                    'team_number' => $teamNumber,
                ]);

                foreach ($definition['trainee_ids'] as $index => $traineeId) {
                    OjtTeamTrainee::query()->create([
                        'ojt_team_id' => $team->id,
                        'trainee_id' => $traineeId,
                        'order' => $index + 1,
                    ]);
                }

                $created->push($team);
                $teamNumber++;
            }
        }

        return [$created, $warnings];
    }

    /**
     * @param  array<int, array{mentor_id: int, trainee_ids: array<int, int>}>  $template
     * @return array<int, array{mentor_id: int, trainee_ids: array<int, int>}>
     */
    private function extractTemplate(OjtSession $session): array
    {
        return $session->teams
            ->sortBy('team_number')
            ->map(function (OjtTeam $team): ?array {
                $trainees = $team->trainees
                    ->sortBy('order')
                    ->pluck('trainee_id')
                    ->map(fn ($id) => (int) $id)
                    ->values()
                    ->all();

                if (count($trainees) !== 2) {
                    return null;
                }

                return [
                    'mentor_id' => (int) $team->mentor_id,
                    'trainee_ids' => $trainees,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, int>  $mentorIds
     * @param  array<int, int>  $traineeIds
     * @param  array<int, array<int, int>>  $pairingCounts
     * @return array{created: Collection<int, OjtTeam>, warnings: array<int, array{type: string, message: string, session_id: int|null}>}
     */
    private function buildTeamsForSession(
        OjtSession $session,
        array $mentorIds,
        array $traineeIds,
        int $teamCount,
        array &$pairingCounts,
    ): array {
        $created = collect();
        $warnings = [];

        $pairs = $this->pairTrainees($traineeIds, $session, $warnings);

        if ($pairs === []) {
            return [
                'created' => $created,
                'warnings' => $warnings,
            ];
        }

        $usedMentors = [];
        $teamNumber = 1;

        foreach ($pairs as $pair) {
            if ($teamNumber > $teamCount) {
                break;
            }

            [$mentorId, $score, $mentorShortage] = $this->pickMentor($mentorIds, $pair, $pairingCounts, $usedMentors);

            if ($mentorShortage) {
                $warnings[] = $this->warning('mentor_shortage', 'Not enough mentors to cover all trainee pairs; mentors will be reused.', $session->id);
            }

            if ($score > 0) {
                $warnings[] = $this->warning('repeat_pair', 'Mentor has previous history with assigned trainee(s).', $session->id);
            }

            $team = OjtTeam::query()->create([
                'ojt_session_id' => $session->id,
                'mentor_id' => $mentorId,
                'team_number' => $teamNumber,
            ]);

            foreach ($pair as $index => $traineeId) {
                OjtTeamTrainee::query()->create([
                    'ojt_team_id' => $team->id,
                    'trainee_id' => $traineeId,
                    'order' => $index + 1,
                ]);

                $pairingCounts[$mentorId][$traineeId] = ($pairingCounts[$mentorId][$traineeId] ?? 0) + 1;
            }

            $created->push($team);
            $usedMentors[] = $mentorId;
            $teamNumber++;
        }

        return [
            'created' => $created,
            'warnings' => $warnings,
        ];
    }

    /**
     * @param  array<int, int>  $mentorIds
     * @param  array<int, int>  $pair
     * @param  array<int, array<int, int>>  $pairingCounts
     * @param  array<int, int>  $usedMentors
     * @return array{0: int, 1: int, 2: bool}
     */
    private function pickMentor(array $mentorIds, array $pair, array $pairingCounts, array $usedMentors): array
    {
        $availableMentors = array_values(array_diff($mentorIds, $usedMentors));
        $mentorShortage = false;

        if ($availableMentors === []) {
            $availableMentors = $mentorIds;
            $mentorShortage = true;
        }

        $bestMentor = $availableMentors[0];
        $bestScore = $this->pairScore($bestMentor, $pair, $pairingCounts);

        foreach ($availableMentors as $mentorId) {
            $score = $this->pairScore($mentorId, $pair, $pairingCounts);

            if ($score < $bestScore) {
                $bestScore = $score;
                $bestMentor = $mentorId;
            }
        }

        return [$bestMentor, $bestScore, $mentorShortage];
    }

    /**
     * @param  array<int, int>  $pair
     * @param  array<int, array<int, int>>  $pairingCounts
     */
    private function pairScore(int $mentorId, array $pair, array $pairingCounts): int
    {
        $score = 0;

        foreach ($pair as $traineeId) {
            $score += $pairingCounts[$mentorId][$traineeId] ?? 0;
        }

        return $score;
    }

    /**
     * @param  array<int, int>  $traineeIds
     * @param  array<int, array{type: string, message: string, session_id: int|null}>  $warnings
     * @return array<int, array<int, int>>
     */
    private function pairTrainees(array $traineeIds, OjtSession $session, array &$warnings): array
    {
        $pairs = [];
        $count = count($traineeIds);

        if ($count < 2) {
            return $pairs;
        }

        if ($count % 2 !== 0) {
            $warnings[] = $this->warning('trainee_leftover', 'One trainee was left without a team.', $session->id);
        }

        for ($i = 0; $i + 1 < $count; $i += 2) {
            $pairs[] = [$traineeIds[$i], $traineeIds[$i + 1]];
        }

        return $pairs;
    }

    /**
     * @param  array<int, array<int, int>>  $pairingCounts
     */
    private function applyExistingPairsToCounts(OjtSession $session, array &$pairingCounts): void
    {
        foreach ($session->teams as $team) {
            foreach ($team->trainees as $trainee) {
                $mentorId = (int) $team->mentor_id;
                $traineeId = (int) $trainee->trainee_id;

                $pairingCounts[$mentorId][$traineeId] = ($pairingCounts[$mentorId][$traineeId] ?? 0) + 1;
            }
        }
    }

    /**
     * @return array<int, array<int, int>>
     */
    private function pairingCounts(Training $training): array
    {
        $pairs = OjtTeamTrainee::query()
            ->select('ojt_teams.mentor_id', 'ojt_team_trainees.trainee_id')
            ->join('ojt_teams', 'ojt_teams.id', '=', 'ojt_team_trainees.ojt_team_id')
            ->join('ojt_sessions', 'ojt_sessions.id', '=', 'ojt_teams.ojt_session_id')
            ->where('ojt_sessions.training_id', $training->id)
            ->get();

        $counts = [];

        foreach ($pairs as $pair) {
            $mentorId = (int) $pair->mentor_id;
            $traineeId = (int) $pair->trainee_id;

            if (! isset($counts[$mentorId])) {
                $counts[$mentorId] = [];
            }

            $counts[$mentorId][$traineeId] = ($counts[$mentorId][$traineeId] ?? 0) + 1;
        }

        return $counts;
    }

    /**
     * @return array{type: string, message: string, session_id: int|null}
     */
    private function warning(string $type, string $message, ?int $sessionId): array
    {
        return [
            'type' => $type,
            'message' => $message,
            'session_id' => $sessionId,
        ];
    }
}
