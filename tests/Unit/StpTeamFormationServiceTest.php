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

    $student1 = User::factory()->create(['name' => 'Aluno 01']);
    $student2 = User::factory()->create(['name' => 'Aluno 02']);
    $student3 = User::factory()->create(['name' => 'Aluno 03']);
    $student4 = User::factory()->create(['name' => 'Aluno 04']);
    $student5 = User::factory()->create(['name' => 'Aluno 98']);
    $student6 = User::factory()->create(['name' => 'Aluno 99']);

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
