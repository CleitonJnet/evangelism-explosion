<?php

namespace App\Services\Stp;

use App\Models\StpSession;
use App\Models\StpTeam;
use App\Models\Training;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StpTeamFormationService
{
    public function formTeams(StpSession $session, bool $randomizeMentors = false): void
    {
        $training = $session->training->loadMissing([
            'course',
            'mentors',
            'students',
            'stpSessions.teams.students',
        ]);

        $mentors = $training->mentors
            ->sortBy(fn (User $mentor): string => mb_strtolower($mentor->name).'#'.$mentor->id)
            ->values();
        if ($randomizeMentors) {
            $mentors = $mentors->shuffle()->values();
        }
        $students = $training->students->values();

        $teamsCount = $this->resolveTeamsCount($mentors->count(), $students->count());

        if ($teamsCount < 1) {
            throw new RuntimeException('Não foi possível formar equipes: é necessário ao menos 1 mentor e 2 alunos.');
        }

        $selectedMentors = $mentors->take($teamsCount)->values();
        $mentorGenderById = $selectedMentors
            ->mapWithKeys(fn (User $mentor): array => [$mentor->id => $this->normalizeGender($mentor->gender)])
            ->all();
        $participationByStudent = $this->loadParticipationByStudent($training);
        $lastMentorByStudent = $this->loadLastMentorByStudent($training, $session);
        $orderedStudents = $this->sortStudentsForDistribution($students, $participationByStudent);
        $preferSameMentor = (int) ($training->course?->execution ?? 0) !== 0;

        DB::transaction(function () use (
            $session,
            $selectedMentors,
            $orderedStudents,
            $lastMentorByStudent,
            $preferSameMentor,
            $mentorGenderById,
        ): void {
            $session->teams()->delete();

            $teams = $this->createTeams($session, $selectedMentors);
            $distribution = $this->distributeStudents(
                $orderedStudents,
                $teams,
                $lastMentorByStudent,
                $preferSameMentor,
                $mentorGenderById,
            );
            $this->persistDistribution($distribution);
        });
    }

    private function resolveTeamsCount(int $mentorsCount, int $studentsCount): int
    {
        return min($mentorsCount, intdiv($studentsCount, 2));
    }

    /**
     * @return array<int, int>
     */
    private function loadParticipationByStudent(Training $training): array
    {
        return DB::table('stp_team_students')
            ->join('stp_teams', 'stp_teams.id', '=', 'stp_team_students.stp_team_id')
            ->join('stp_sessions', 'stp_sessions.id', '=', 'stp_teams.stp_session_id')
            ->where('stp_sessions.training_id', $training->id)
            ->groupBy('stp_team_students.user_id')
            ->selectRaw('stp_team_students.user_id as user_id, COUNT(DISTINCT stp_sessions.id) as sessions_count')
            ->pluck('sessions_count', 'user_id')
            ->map(fn (mixed $count): int => (int) $count)
            ->toArray();
    }

    /**
     * @return array<int, int>
     */
    private function loadLastMentorByStudent(Training $training, StpSession $currentSession): array
    {
        $lastSessionId = DB::table('stp_sessions')
            ->where('training_id', $training->id)
            ->where('sequence', '<', $currentSession->sequence)
            ->orderByDesc('sequence')
            ->value('id');

        if (! $lastSessionId) {
            return [];
        }

        return DB::table('stp_team_students')
            ->join('stp_teams', 'stp_teams.id', '=', 'stp_team_students.stp_team_id')
            ->where('stp_teams.stp_session_id', (int) $lastSessionId)
            ->pluck('stp_teams.mentor_user_id', 'stp_team_students.user_id')
            ->map(fn (mixed $mentorId): int => (int) $mentorId)
            ->toArray();
    }

    /**
     * @param  Collection<int, User>  $students
     * @param  array<int, int>  $participationByStudent
     * @return Collection<int, User>
     */
    private function sortStudentsForDistribution(Collection $students, array $participationByStudent): Collection
    {
        return $students
            ->sortBy(function (User $student) use ($participationByStudent): string {
                $participationCount = $participationByStudent[$student->id] ?? 0;

                return str_pad((string) $participationCount, 5, '0', STR_PAD_LEFT).'#'.mb_strtolower($student->name).'#'.$student->id;
            })
            ->values();
    }

    /**
     * @param  Collection<int, User>  $selectedMentors
     * @return Collection<int, StpTeam>
     */
    private function createTeams(StpSession $session, Collection $selectedMentors): Collection
    {
        return $selectedMentors
            ->values()
            ->map(function (User $mentor, int $index) use ($session): StpTeam {
                return StpTeam::query()->create([
                    'stp_session_id' => $session->id,
                    'mentor_user_id' => $mentor->id,
                    'name' => sprintf('Equipe %02d', $index + 1),
                    'position' => $index,
                ]);
            });
    }

    /**
     * @param  Collection<int, User>  $orderedStudents
     * @param  Collection<int, StpTeam>  $teams
     * @param  array<int, int>  $lastMentorByStudent
     * @param  array<int, ?string>  $mentorGenderById
     * @return array<int, array{team: StpTeam, students: array<int, User>}>
     */
    private function distributeStudents(
        Collection $orderedStudents,
        Collection $teams,
        array $lastMentorByStudent,
        bool $preferSameMentor,
        array $mentorGenderById,
    ): array {
        $distribution = [];
        $teamIds = array_values($teams->pluck('id')->all());
        $targetStudentsByTeamId = $this->resolveTargetStudentsByTeam($teamIds, $orderedStudents->count());

        foreach ($teams as $team) {
            $distribution[$team->id] = [
                'team' => $team,
                'students' => [],
            ];
        }

        $genderGroups = $this->splitStudentsByGender($orderedStudents);
        $femaleQueue = $genderGroups['female'];
        $maleQueue = $genderGroups['male'];
        $unknownQueue = $genderGroups['unknown'];

        $this->assignRequiredOppositeGenderStudents(
            $distribution,
            $teams,
            $femaleQueue,
            $maleQueue,
            $lastMentorByStudent,
            $preferSameMentor,
            $mentorGenderById,
            $targetStudentsByTeamId,
        );

        $remainingGroups = $this->sortGenderGroupsByPriority($femaleQueue, $maleQueue, $unknownQueue);

        foreach ($remainingGroups as $group) {
            if ($group->isEmpty()) {
                continue;
            }

            $this->distributeGroupByRoundRobin(
                $distribution,
                $teams,
                $group,
                $lastMentorByStudent,
                $preferSameMentor,
                $targetStudentsByTeamId,
            );
        }

        return $distribution;
    }

    /**
     * @param  array<int, array{team: StpTeam, students: array<int, User>}>  $distribution
     * @param  Collection<int, StpTeam>  $teams
     * @param  Collection<int, User>  $femaleQueue
     * @param  Collection<int, User>  $maleQueue
     * @param  array<int, int>  $lastMentorByStudent
     * @param  array<int, ?string>  $mentorGenderById
     * @param  array<int, int>  $targetStudentsByTeamId
     */
    private function assignRequiredOppositeGenderStudents(
        array &$distribution,
        Collection $teams,
        Collection &$femaleQueue,
        Collection &$maleQueue,
        array $lastMentorByStudent,
        bool $preferSameMentor,
        array $mentorGenderById,
        array $targetStudentsByTeamId,
    ): void {
        foreach ($teams as $team) {
            $teamId = $team->id;

            if (count($distribution[$teamId]['students']) >= ($targetStudentsByTeamId[$teamId] ?? 0)) {
                continue;
            }

            $mentorGender = $mentorGenderById[(int) $team->mentor_user_id] ?? null;

            if ($mentorGender === 'M' && $femaleQueue->isNotEmpty()) {
                $distribution[$teamId]['students'][] = $this->pickStudentForTeam(
                    $femaleQueue,
                    (int) $team->mentor_user_id,
                    $lastMentorByStudent,
                    $preferSameMentor,
                );
            }

            if (
                $mentorGender === 'F'
                && $maleQueue->isNotEmpty()
                && count($distribution[$teamId]['students']) < ($targetStudentsByTeamId[$teamId] ?? 0)
            ) {
                $distribution[$teamId]['students'][] = $this->pickStudentForTeam(
                    $maleQueue,
                    (int) $team->mentor_user_id,
                    $lastMentorByStudent,
                    $preferSameMentor,
                );
            }
        }
    }

    /**
     * @param  array<int, array{team: StpTeam, students: array<int, User>}>  $distribution
     * @param  Collection<int, StpTeam>  $teams
     * @param  Collection<int, User>  $group
     * @param  array<int, int>  $lastMentorByStudent
     * @param  array<int, int>  $targetStudentsByTeamId
     */
    private function distributeGroupByRoundRobin(
        array &$distribution,
        Collection $teams,
        Collection $group,
        array $lastMentorByStudent,
        bool $preferSameMentor,
        array $targetStudentsByTeamId,
    ): void {
        $queue = $group->values();
        $teamIds = array_values($teams->pluck('id')->all());
        $teamCount = count($teamIds);
        $teamIndex = 0;

        while ($queue->isNotEmpty()) {
            $teamId = $this->pickNextTeamIdWithCapacity(
                $distribution,
                $teamIds,
                $targetStudentsByTeamId,
                $teamIndex,
            );

            if ($teamId === null) {
                break;
            }

            /** @var StpTeam $team */
            $team = $distribution[$teamId]['team'];
            $student = $this->pickStudentForTeam($queue, $team->mentor_user_id, $lastMentorByStudent, $preferSameMentor);
            $distribution[$teamId]['students'][] = $student;
            $currentTeamIndex = (int) array_search($teamId, $teamIds, true);
            $teamIndex = ($currentTeamIndex + 1) % $teamCount;
        }
    }

    /**
     * @param  Collection<int, User>  $students
     * @return array{
     *     first: Collection<int, User>,
     *     second: Collection<int, User>,
     *     unknown: Collection<int, User>
     * }
     */
    private function splitStudentsByGender(Collection $students): array
    {
        $femaleStudents = $students->filter(
            fn (User $student): bool => $this->normalizeGender($student->gender) === 'F',
        )->values();
        $maleStudents = $students->filter(
            fn (User $student): bool => $this->normalizeGender($student->gender) === 'M',
        )->values();
        $unknownGenderStudents = $students->filter(
            fn (User $student): bool => $this->normalizeGender($student->gender) === null,
        )->values();

        return [
            'female' => $femaleStudents,
            'male' => $maleStudents,
            'unknown' => $unknownGenderStudents,
        ];
    }

    /**
     * @param  Collection<int, User>  $femaleStudents
     * @param  Collection<int, User>  $maleStudents
     * @param  Collection<int, User>  $unknownGenderStudents
     * @return array{
     *     first: Collection<int, User>,
     *     second: Collection<int, User>,
     *     unknown: Collection<int, User>
     * }
     */
    private function sortGenderGroupsByPriority(
        Collection $femaleStudents,
        Collection $maleStudents,
        Collection $unknownGenderStudents,
    ): array {
        if ($femaleStudents->count() >= $maleStudents->count()) {
            return [
                'first' => $femaleStudents,
                'second' => $maleStudents,
                'unknown' => $unknownGenderStudents,
            ];
        }

        return [
            'first' => $maleStudents,
            'second' => $femaleStudents,
            'unknown' => $unknownGenderStudents,
        ];
    }

    /**
     * @param  array<int, int>  $teamIds
     * @return array<int, int>
     */
    private function resolveTargetStudentsByTeam(array $teamIds, int $studentsCount): array
    {
        $teamsCount = count($teamIds);
        $base = intdiv($studentsCount, $teamsCount);
        $remainder = $studentsCount % $teamsCount;
        $targetByTeamId = [];

        foreach ($teamIds as $index => $teamId) {
            $targetByTeamId[$teamId] = $base + ($index < $remainder ? 1 : 0);
        }

        return $targetByTeamId;
    }

    /**
     * @param  array<int, array{team: StpTeam, students: array<int, User>}>  $distribution
     * @param  array<int, int>  $teamIds
     * @param  array<int, int>  $targetStudentsByTeamId
     */
    private function pickNextTeamIdWithCapacity(
        array $distribution,
        array $teamIds,
        array $targetStudentsByTeamId,
        int $startIndex,
    ): ?int {
        $teamsCount = count($teamIds);

        for ($offset = 0; $offset < $teamsCount; $offset++) {
            $index = ($startIndex + $offset) % $teamsCount;
            $teamId = $teamIds[$index];
            $currentCount = count($distribution[$teamId]['students']);

            if ($currentCount < ($targetStudentsByTeamId[$teamId] ?? 0)) {
                return $teamId;
            }
        }

        return null;
    }

    private function normalizeGender(mixed $gender): ?string
    {
        return User::genderCodeFromValue($gender);
    }

    /**
     * @param  Collection<int, User>  $queue
     * @param  array<int, int>  $lastMentorByStudent
     */
    private function pickStudentForTeam(
        Collection &$queue,
        int $mentorUserId,
        array $lastMentorByStudent,
        bool $preferSameMentor,
    ): User {
        $index = $queue->search(function (User $student) use ($mentorUserId, $lastMentorByStudent, $preferSameMentor): bool {
            $lastMentorId = $lastMentorByStudent[$student->id] ?? null;

            if ($lastMentorId === null) {
                return true;
            }

            if ($preferSameMentor) {
                return $lastMentorId === $mentorUserId;
            }

            return $lastMentorId !== $mentorUserId;
        });

        $index = $index === false ? 0 : (int) $index;
        /** @var User $student */
        $student = $queue->get($index);
        $queue->forget($index);
        $queue = $queue->values();

        return $student;
    }

    /**
     * @param  array<int, array{team: StpTeam, students: array<int, User>}>  $distribution
     */
    private function persistDistribution(array $distribution): void
    {
        foreach ($distribution as $teamData) {
            $team = $teamData['team'];

            foreach ($teamData['students'] as $position => $student) {
                $team->students()->attach($student->id, ['position' => $position]);
            }
        }
    }
}
