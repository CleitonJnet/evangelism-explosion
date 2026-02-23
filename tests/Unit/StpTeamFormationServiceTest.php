<?php

use App\Models\Church;
use App\Models\Course;
use App\Models\StpSession;
use App\Models\StpTeam;
use App\Models\Training;
use App\Models\User;
use App\Services\Stp\StpTeamFormationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function createTrainingForStpFormation(?Course $course = null): array
{
    $course ??= Course::factory()->create([
        'execution' => 0,
        'min_stp_sessions' => 0,
    ]);

    $church = Church::factory()->create();
    $teacher = User::factory()->create();

    $training = Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
    ]);

    return [$training, $teacher];
}

it('forms at most min mentors and half students teams', function (): void {
    [$training, $teacher] = createTrainingForStpFormation();

    $mentors = User::factory()->count(3)->create();
    $students = User::factory()->count(5)->create();

    foreach ($mentors as $mentor) {
        $training->mentors()->attach($mentor->id, ['created_by' => $teacher->id]);
    }

    $training->students()->attach($students->pluck('id')->all());

    $session = StpSession::factory()->create([
        'training_id' => $training->id,
        'sequence' => 1,
    ]);

    app(StpTeamFormationService::class)->formTeams($session);

    $teams = StpTeam::query()
        ->where('stp_session_id', $session->id)
        ->withCount('students')
        ->get();

    expect($teams)->toHaveCount(2);
    expect($teams->sum('students_count'))->toBe(5);
});

it('prioritizes students with fewer previous participations', function (): void {
    [$training, $teacher] = createTrainingForStpFormation();

    $mentorA = User::factory()->create(['name' => 'Mentor A']);
    $mentorB = User::factory()->create(['name' => 'Mentor B']);
    $training->mentors()->attach($mentorA->id, ['created_by' => $teacher->id]);
    $training->mentors()->attach($mentorB->id, ['created_by' => $teacher->id]);

    $student1 = User::factory()->create(['name' => 'Aluno 01', 'gender' => null]);
    $student2 = User::factory()->create(['name' => 'Aluno 02', 'gender' => null]);
    $student3 = User::factory()->create(['name' => 'Aluno 03', 'gender' => null]);
    $student4 = User::factory()->create(['name' => 'Aluno 04', 'gender' => null]);
    $student5 = User::factory()->create(['name' => 'Aluno 98', 'gender' => null]);
    $student6 = User::factory()->create(['name' => 'Aluno 99', 'gender' => null]);

    $training->students()->attach([
        $student1->id,
        $student2->id,
        $student3->id,
        $student4->id,
        $student5->id,
        $student6->id,
    ]);

    $oldSession = StpSession::factory()->create([
        'training_id' => $training->id,
        'sequence' => 1,
    ]);

    $oldTeamA = StpTeam::factory()->create([
        'stp_session_id' => $oldSession->id,
        'mentor_user_id' => $mentorA->id,
        'position' => 0,
    ]);
    $oldTeamB = StpTeam::factory()->create([
        'stp_session_id' => $oldSession->id,
        'mentor_user_id' => $mentorB->id,
        'position' => 1,
    ]);

    $oldTeamA->students()->attach($student5->id, ['position' => 0]);
    $oldTeamB->students()->attach($student6->id, ['position' => 0]);

    StpSession::factory()->create([
        'training_id' => $training->id,
        'sequence' => 2,
    ]);

    $currentSession = StpSession::factory()->create([
        'training_id' => $training->id,
        'sequence' => 3,
    ]);

    app(StpTeamFormationService::class)->formTeams($currentSession);

    $highParticipationRows = collect([$student5->id, $student6->id])
        ->map(function (int $studentId): ?\stdClass {
            return DB::table('stp_team_students')
                ->where('user_id', $studentId)
                ->orderByDesc('id')
                ->first(['position']);
        });

    expect($highParticipationRows->every(fn ($row): bool => $row !== null && (int) $row->position === 2))->toBeTrue();
});

it('distributes women and men across teams to keep mixed groups when possible', function (): void {
    [$training, $teacher] = createTrainingForStpFormation();

    $mentorA = User::factory()->create(['name' => 'Mentor A', 'gender' => 'M']);
    $mentorB = User::factory()->create(['name' => 'Mentor B', 'gender' => 'F']);
    $mentorC = User::factory()->create(['name' => 'Mentor C', 'gender' => 'M']);

    $training->mentors()->attach([$mentorA->id, $mentorB->id, $mentorC->id], ['created_by' => $teacher->id]);

    $femaleStudents = User::factory()->count(3)->create(['gender' => 'F']);
    $maleStudents = User::factory()->count(3)->create(['gender' => 'M']);

    $training->students()->attach($femaleStudents->pluck('id')->all());
    $training->students()->attach($maleStudents->pluck('id')->all());

    $session = StpSession::factory()->create([
        'training_id' => $training->id,
        'sequence' => 1,
    ]);

    app(StpTeamFormationService::class)->formTeams($session);

    $teams = StpTeam::query()
        ->where('stp_session_id', $session->id)
        ->with(['mentor', 'students'])
        ->orderBy('position')
        ->get();

    expect($teams)->toHaveCount(3);

    foreach ($teams as $team) {
        $combinedGenders = $team->students
            ->pluck('gender')
            ->push((string) ($team->mentor?->gender ?? ''))
            ->map(fn (string $gender): string => strtoupper($gender))
            ->all();
        expect($combinedGenders)->toContain('F');
        expect($combinedGenders)->toContain('M');
    }
});

it('allows homogeneous teams only when one gender count is lower than teams count', function (): void {
    [$training, $teacher] = createTrainingForStpFormation();

    $mentorA = User::factory()->create(['name' => 'Mentor A']);
    $mentorB = User::factory()->create(['name' => 'Mentor B']);
    $mentorC = User::factory()->create(['name' => 'Mentor C']);

    $training->mentors()->attach([$mentorA->id, $mentorB->id, $mentorC->id], ['created_by' => $teacher->id]);

    $femaleStudents = User::factory()->count(2)->create(['gender' => 'Feminino']);
    $maleStudents = User::factory()->count(5)->create(['gender' => 'Masculino']);

    $training->students()->attach($femaleStudents->pluck('id')->all());
    $training->students()->attach($maleStudents->pluck('id')->all());

    $session = StpSession::factory()->create([
        'training_id' => $training->id,
        'sequence' => 1,
    ]);

    app(StpTeamFormationService::class)->formTeams($session);

    $teams = StpTeam::query()
        ->where('stp_session_id', $session->id)
        ->with('students')
        ->orderBy('position')
        ->get();

    expect($teams)->toHaveCount(3);

    $femaleTeamCount = $teams
        ->filter(fn (StpTeam $team): bool => $team->students->contains(fn (User $student): bool => in_array($student->gender, ['F', 'Feminino'], true)))
        ->count();

    expect($femaleTeamCount)->toBe(2);
});

it('considers mentor and students genders to keep each team mixed when possible', function (): void {
    [$training, $teacher] = createTrainingForStpFormation();

    $mentorMale = User::factory()->create(['name' => 'Mentor Homem', 'gender' => 'M']);
    $mentorFemale = User::factory()->create(['name' => 'Mentora Mulher', 'gender' => 'F']);

    $training->mentors()->attach([$mentorMale->id, $mentorFemale->id], ['created_by' => $teacher->id]);

    $femaleStudents = User::factory()->count(2)->create(['gender' => 'F']);
    $maleStudents = User::factory()->count(2)->create(['gender' => 'M']);

    $training->students()->attach($femaleStudents->pluck('id')->all());
    $training->students()->attach($maleStudents->pluck('id')->all());

    $session = StpSession::factory()->create([
        'training_id' => $training->id,
        'sequence' => 1,
    ]);

    app(StpTeamFormationService::class)->formTeams($session);

    $teams = StpTeam::query()
        ->where('stp_session_id', $session->id)
        ->with(['mentor', 'students'])
        ->orderBy('position')
        ->get();

    expect($teams)->toHaveCount(2);

    foreach ($teams as $team) {
        $combinedGenders = $team->students
            ->pluck('gender')
            ->push((string) ($team->mentor?->gender ?? ''))
            ->map(fn (string $gender): string => strtoupper($gender))
            ->all();

        expect($combinedGenders)->toContain('F');
        expect($combinedGenders)->toContain('M');
    }
});

it('distributes students uniformly between mentors when randomizing', function (): void {
    [$training, $teacher] = createTrainingForStpFormation();

    $mentors = User::factory()->count(3)->create();
    $students = User::factory()->count(8)->create();

    foreach ($mentors as $mentor) {
        $training->mentors()->attach($mentor->id, ['created_by' => $teacher->id]);
    }

    $training->students()->attach($students->pluck('id')->all());

    $session = StpSession::factory()->create([
        'training_id' => $training->id,
        'sequence' => 1,
    ]);

    app(StpTeamFormationService::class)->formTeams($session, true);

    $teams = StpTeam::query()
        ->where('stp_session_id', $session->id)
        ->withCount('students')
        ->orderBy('position')
        ->get();

    expect($teams)->toHaveCount(3);

    $counts = $teams->pluck('students_count')->map(fn (int $count): int => (int) $count)->all();
    $maxCount = max($counts);
    $minCount = min($counts);

    expect($maxCount - $minCount)->toBeLessThanOrEqual(1);
});
