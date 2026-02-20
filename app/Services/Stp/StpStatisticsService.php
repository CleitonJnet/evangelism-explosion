<?php

namespace App\Services\Stp;

use App\Enums\StpApproachResult;
use App\Enums\StpApproachStatus;
use App\Enums\StpApproachType;
use App\Models\StpApproach;
use App\Models\StpSession;
use App\Models\Training;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StpStatisticsService
{
    /**
     * @return array<int, array{
     *     team_id: int,
     *     team_name: string,
     *     mentor_user_id: int,
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
    public function teamStats(StpSession $session): array
    {
        $session->loadMissing('teams');

        $statsByTeam = [];

        foreach ($session->teams as $team) {
            $statsByTeam[$team->id] = [
                'team_id' => $team->id,
                'team_name' => $team->name,
                'mentor_user_id' => (int) $team->mentor_user_id,
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
            ];
        }

        $approaches = StpApproach::query()
            ->where('stp_session_id', $session->id)
            ->whereIn('status', [StpApproachStatus::Done->value, StpApproachStatus::Reviewed->value])
            ->whereNotNull('stp_team_id')
            ->get();

        foreach ($approaches as $approach) {
            $teamId = (int) $approach->stp_team_id;

            if (! isset($statsByTeam[$teamId])) {
                continue;
            }

            $typeKey = $approach->type->value;

            if ($typeKey === StpApproachType::Visitor->value) {
                $statsByTeam[$teamId]['visitant']++;
            }

            if ($typeKey === StpApproachType::SecurityQuestionnaire->value) {
                $statsByTeam[$teamId]['questionnaire']++;
            }

            if ($typeKey === StpApproachType::Indication->value) {
                $statsByTeam[$teamId]['indication']++;
            }

            if ($typeKey === StpApproachType::Lifestyle->value) {
                $statsByTeam[$teamId]['lifeway']++;
            }

            $statsByTeam[$teamId]['totExplained'] += (int) ($approach->gospel_explained_times ?? 0);
            $listeners = collect(data_get($approach->payload, 'listeners', []))
                ->filter(fn (mixed $listener): bool => is_array($listener))
                ->values();

            if ($listeners->isNotEmpty()) {
                $statsByTeam[$teamId]['totPeople'] += $listeners->count();

                foreach ($listeners as $listener) {
                    $resultKey = data_get($listener, 'result');
                    $this->incrementResultTotals($statsByTeam[$teamId], is_string($resultKey) ? $resultKey : null);
                }
            } else {
                $statsByTeam[$teamId]['totPeople'] += (int) ($approach->people_count ?? 0);

                $resultKey = $approach->result?->value;
                $this->incrementResultTotals($statsByTeam[$teamId], $resultKey);
            }

            if ((bool) $approach->means_growth) {
                $statsByTeam[$teamId]['meansGrowth']++;
            }

            if ($approach->follow_up_scheduled_at !== null) {
                $statsByTeam[$teamId]['folowship']++;
            }
        }

        return array_values($statsByTeam);
    }

    /**
     * @param  array<string, mixed>  $teamTotals
     */
    private function incrementResultTotals(array &$teamTotals, ?string $resultKey): void
    {
        if ($resultKey === null) {
            return;
        }

        if ($resultKey === StpApproachResult::Decision->value) {
            $teamTotals['totDecision']++;
        }

        if ($resultKey === StpApproachResult::NoDecisionInterested->value) {
            $teamTotals['totInteresting']++;
        }

        if ($resultKey === StpApproachResult::Rejection->value) {
            $teamTotals['totReject']++;
        }

        if ($resultKey === StpApproachResult::AlreadyChristian->value) {
            $teamTotals['totChristian']++;
        }
    }

    /**
     * @return array<int, array{student_id: int, name: string, participated: int, missing: int}>
     */
    public function studentsBelowMinimum(Training $training): array
    {
        $training->loadMissing(['course', 'students']);

        $minimumSessions = (int) ($training->course?->min_stp_sessions ?? 0);

        if ($minimumSessions <= 0) {
            return [];
        }

        $participationByStudent = DB::table('stp_team_students')
            ->join('stp_teams', 'stp_teams.id', '=', 'stp_team_students.stp_team_id')
            ->join('stp_sessions', 'stp_sessions.id', '=', 'stp_teams.stp_session_id')
            ->where('stp_sessions.training_id', $training->id)
            ->groupBy('stp_team_students.user_id')
            ->selectRaw('stp_team_students.user_id as user_id, COUNT(DISTINCT stp_sessions.id) as sessions_count')
            ->pluck('sessions_count', 'user_id')
            ->map(fn (mixed $count): int => (int) $count)
            ->toArray();

        return $training->students
            ->map(function (User $student) use ($minimumSessions, $participationByStudent): array {
                $participated = $participationByStudent[$student->id] ?? 0;
                $missing = max(0, $minimumSessions - $participated);

                return [
                    'student_id' => $student->id,
                    'name' => $student->name,
                    'participated' => $participated,
                    'missing' => $missing,
                ];
            })
            ->filter(fn (array $item): bool => $item['missing'] > 0)
            ->sortBy(fn (array $item): string => str_pad((string) $item['missing'], 5, '0', STR_PAD_LEFT).'#'.mb_strtolower($item['name']))
            ->values()
            ->all();
    }
}
