<?php

use App\Enums\StpApproachResult;
use App\Enums\StpApproachStatus;
use App\Enums\StpApproachType;
use App\Livewire\Pages\App\Teacher\Training\ManageMentorsModal;
use App\Livewire\Pages\App\Teacher\Training\Statistics;
use App\Models\Church;
use App\Models\Course;
use App\Models\Role;
use App\Models\StpApproach;
use App\Models\StpSession;
use App\Models\StpTeam;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createTeacherForStatisticsPage(): User
{
    $teacher = User::factory()->create(['church_id' => null]);
    $teacherRole = Role::query()->firstOrCreate(['name' => 'Teacher']);
    $teacher->roles()->syncWithoutDetaching([$teacherRole->id]);

    return $teacher;
}

function createTrainingForStatisticsPage(User $teacher): Training
{
    $church = Church::factory()->create();

    return Training::factory()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
    ]);
}

it('renders the teacher training statistics page with the livewire component', function () {
    $teacher = createTeacherForStatisticsPage();
    $training = createTrainingForStatisticsPage($teacher);

    $response = $this
        ->actingAs($teacher)
        ->get(route('app.teacher.trainings.statistics', $training));

    $response->assertOk();
    $response->assertSeeLivewire(Statistics::class);
    $response->assertSeeLivewire(ManageMentorsModal::class);
    $response->assertSeeText('Integrantes das Equipes');
    $response->assertSeeText('Mentores');
});

it('creates a stp session and sets it as active', function () {
    $teacher = createTeacherForStatisticsPage();
    $training = createTrainingForStatisticsPage($teacher);
    $mentor = User::factory()->create();
    $student = User::factory()->create();

    $training->mentors()->attach($mentor->id, ['created_by' => $teacher->id]);
    $training->students()->attach($student->id);

    Livewire::actingAs($teacher)
        ->test(Statistics::class, ['training' => $training])
        ->call('createSession')
        ->assertSet('activeSessionId', fn ($value): bool => is_int($value) && $value > 0)
        ->assertSet('sessions.0.label', 'Sessão 1');

    expect(StpSession::query()->where('training_id', $training->id)->count())->toBe(1);
});

it('does not create stp session without mentors and students in training', function () {
    $teacher = createTeacherForStatisticsPage();
    $training = createTrainingForStatisticsPage($teacher);

    Livewire::actingAs($teacher)
        ->test(Statistics::class, ['training' => $training])
        ->call('createSession')
        ->assertHasErrors(['sessionCreation']);

    expect(StpSession::query()->where('training_id', $training->id)->count())->toBe(0);
});

it('does not create new session when previous session has no teams or students', function () {
    $teacher = createTeacherForStatisticsPage();
    $training = createTrainingForStatisticsPage($teacher);
    $mentor = User::factory()->create();
    $student = User::factory()->create();

    $training->mentors()->attach($mentor->id, ['created_by' => $teacher->id]);
    $training->students()->attach($student->id);

    StpSession::query()->create([
        'training_id' => $training->id,
        'sequence' => 1,
    ]);

    Livewire::actingAs($teacher)
        ->test(Statistics::class, ['training' => $training])
        ->call('createSession')
        ->assertHasErrors(['sessionCreation']);

    expect(StpSession::query()->where('training_id', $training->id)->count())->toBe(1);
});

it('forms teams for the active session using mentors and students', function () {
    $teacher = createTeacherForStatisticsPage();
    $training = createTrainingForStatisticsPage($teacher);

    $mentorA = User::factory()->create(['name' => 'Mentor A']);
    $mentorB = User::factory()->create(['name' => 'Mentor B']);
    $training->mentors()->attach($mentorA->id, ['created_by' => $teacher->id]);
    $training->mentors()->attach($mentorB->id, ['created_by' => $teacher->id]);

    $students = User::factory()->count(4)->create();

    foreach ($students as $student) {
        $training->students()->attach($student->id);
    }

    $session = StpSession::query()->create([
        'training_id' => $training->id,
        'sequence' => 1,
        'label' => null,
        'starts_at' => null,
        'ends_at' => null,
        'status' => null,
    ]);

    Livewire::actingAs($teacher)
        ->test(Statistics::class, ['training' => $training])
        ->call('selectSession', $session->id)
        ->call('formTeams')
        ->assertSet('teams.0.students.0.id', fn ($value): bool => is_int($value));

    $session->refresh();
    $teams = $session->teams()->withCount('students')->get();

    expect($teams)->toHaveCount(2);
    expect($teams->sum('students_count'))->toBe(4);
});

it('moves students between teams and persists destination ordering', function () {
    $teacher = createTeacherForStatisticsPage();
    $training = createTrainingForStatisticsPage($teacher);

    $mentorA = User::factory()->create(['name' => 'Mentor A']);
    $mentorB = User::factory()->create(['name' => 'Mentor B']);
    $studentA = User::factory()->create(['name' => 'Aluno A']);
    $studentB = User::factory()->create(['name' => 'Aluno B']);
    $studentC = User::factory()->create(['name' => 'Aluno C']);

    $training->students()->attach([$studentA->id, $studentB->id, $studentC->id]);

    $session = StpSession::query()->create([
        'training_id' => $training->id,
        'sequence' => 1,
    ]);

    $teamOne = StpTeam::query()->create([
        'stp_session_id' => $session->id,
        'mentor_user_id' => $mentorA->id,
        'name' => 'Equipe 01',
        'position' => 0,
    ]);
    $teamTwo = StpTeam::query()->create([
        'stp_session_id' => $session->id,
        'mentor_user_id' => $mentorB->id,
        'name' => 'Equipe 02',
        'position' => 1,
    ]);

    $teamOne->students()->attach($studentA->id, ['position' => 0]);
    $teamOne->students()->attach($studentB->id, ['position' => 1]);
    $teamTwo->students()->attach($studentC->id, ['position' => 0]);

    Livewire::actingAs($teacher)
        ->test(Statistics::class, ['training' => $training])
        ->call('selectSession', $session->id)
        ->call('moveStudent', $studentB->id, $teamOne->id, $teamTwo->id, null);

    expect($teamOne->students()->pluck('users.id')->all())->not->toContain($studentB->id);

    $orderedTeamTwoIds = $teamTwo->students()
        ->orderBy('stp_team_students.position')
        ->pluck('users.id')
        ->all();

    expect($orderedTeamTwoIds)->toBe([$studentB->id, $studentC->id]);
});

it('swaps mentors between teams and keeps students intact', function () {
    $teacher = createTeacherForStatisticsPage();
    $training = createTrainingForStatisticsPage($teacher);

    $mentorA = User::factory()->create(['name' => 'Mentor A']);
    $mentorB = User::factory()->create(['name' => 'Mentor B']);
    $studentA = User::factory()->create(['name' => 'Aluno A']);
    $studentB = User::factory()->create(['name' => 'Aluno B']);

    $training->students()->attach([$studentA->id, $studentB->id]);

    $session = StpSession::query()->create([
        'training_id' => $training->id,
        'sequence' => 1,
    ]);

    $teamOne = StpTeam::query()->create([
        'stp_session_id' => $session->id,
        'mentor_user_id' => $mentorA->id,
        'name' => 'Equipe 01',
        'position' => 0,
    ]);
    $teamTwo = StpTeam::query()->create([
        'stp_session_id' => $session->id,
        'mentor_user_id' => $mentorB->id,
        'name' => 'Equipe 02',
        'position' => 1,
    ]);

    $teamOne->students()->attach($studentA->id, ['position' => 0]);
    $teamTwo->students()->attach($studentB->id, ['position' => 0]);

    Livewire::actingAs($teacher)
        ->test(Statistics::class, ['training' => $training])
        ->call('selectSession', $session->id)
        ->call('swapMentor', $mentorA->id, $teamOne->id, $teamTwo->id);

    expect($teamOne->fresh()->mentor_user_id)->toBe($mentorB->id);
    expect($teamTwo->fresh()->mentor_user_id)->toBe($mentorA->id);
    expect($teamOne->students()->count())->toBe(1);
    expect($teamTwo->students()->count())->toBe(1);
});

it('assigns a new mentor to a team from training mentors without removing from other teams', function () {
    $teacher = createTeacherForStatisticsPage();
    $training = createTrainingForStatisticsPage($teacher);

    $mentorA = User::factory()->create(['name' => 'Mentor A']);
    $mentorB = User::factory()->create(['name' => 'Mentor B']);
    $mentorC = User::factory()->create(['name' => 'Mentor C']);

    $training->mentors()->attach($mentorA->id, ['created_by' => $teacher->id]);
    $training->mentors()->attach($mentorB->id, ['created_by' => $teacher->id]);
    $training->mentors()->attach($mentorC->id, ['created_by' => $teacher->id]);

    $session = StpSession::query()->create([
        'training_id' => $training->id,
        'sequence' => 1,
    ]);

    $teamOne = StpTeam::query()->create([
        'stp_session_id' => $session->id,
        'mentor_user_id' => $mentorA->id,
        'name' => 'Equipe 01',
        'position' => 0,
    ]);

    $teamTwo = StpTeam::query()->create([
        'stp_session_id' => $session->id,
        'mentor_user_id' => $mentorB->id,
        'name' => 'Equipe 02',
        'position' => 1,
    ]);

    Livewire::actingAs($teacher)
        ->test(Statistics::class, ['training' => $training])
        ->call('selectSession', $session->id)
        ->call('openMentorSelector', $teamTwo->id)
        ->assertSet('showMentorSelectorModal', true)
        ->assertSet('mentorSelectionCurrentMentorId', $mentorB->id)
        ->assertSet(
            'mentorSelectionOptions',
            fn (array $options): bool => collect($options)->pluck('id')->sort()->values()->all() === [$mentorA->id, $mentorC->id],
        )
        ->assertSet('selectedMentorId', $mentorA->id)
        ->set('selectedMentorId', $mentorA->id)
        ->call('assignMentorToTeam')
        ->assertSet('showMentorSelectorModal', false);

    expect($teamOne->fresh()->mentor_user_id)->toBe($mentorA->id);
    expect($teamTwo->fresh()->mentor_user_id)->toBe($mentorA->id);
});

it('calculates real team totals from stp approaches', function () {
    $teacher = createTeacherForStatisticsPage();
    $training = createTrainingForStatisticsPage($teacher);

    $mentor = User::factory()->create(['name' => 'Mentor A']);
    $creator = User::factory()->create();

    $session = StpSession::query()->create([
        'training_id' => $training->id,
        'sequence' => 1,
    ]);

    $team = StpTeam::query()->create([
        'stp_session_id' => $session->id,
        'mentor_user_id' => $mentor->id,
        'name' => 'Equipe 01',
        'position' => 0,
    ]);

    StpApproach::query()->create([
        'training_id' => $training->id,
        'stp_session_id' => $session->id,
        'stp_team_id' => $team->id,
        'type' => StpApproachType::Visitor->value,
        'status' => StpApproachStatus::Done->value,
        'position' => 0,
        'person_name' => 'Pessoa 1',
        'gospel_explained_times' => 2,
        'people_count' => 3,
        'result' => StpApproachResult::Decision->value,
        'means_growth' => true,
        'follow_up_scheduled_at' => now(),
        'created_by_user_id' => $creator->id,
    ]);

    StpApproach::query()->create([
        'training_id' => $training->id,
        'stp_session_id' => $session->id,
        'stp_team_id' => $team->id,
        'type' => StpApproachType::Indication->value,
        'status' => StpApproachStatus::Reviewed->value,
        'position' => 1,
        'person_name' => 'Pessoa 2',
        'gospel_explained_times' => 1,
        'people_count' => 2,
        'result' => StpApproachResult::NoDecisionInterested->value,
        'means_growth' => false,
        'created_by_user_id' => $creator->id,
    ]);

    Livewire::actingAs($teacher)
        ->test(Statistics::class, ['training' => $training])
        ->call('selectSession', $session->id)
        ->assertSet('columnTotals.0', 1)
        ->assertSet('columnTotals.1', 0)
        ->assertSet('columnTotals.2', 1)
        ->assertSet('columnTotals.3', 0)
        ->assertSet('columnTotals.4', 3)
        ->assertSet('columnTotals.5', 5)
        ->assertSet('columnTotals.6', 1)
        ->assertSet('columnTotals.7', 1)
        ->assertSet('columnTotals.8', 0)
        ->assertSet('columnTotals.9', 0)
        ->assertSet('columnTotals.10', 1)
        ->assertSet('columnTotals.11', 1);
});

it('loads pending students below minimum stp sessions', function () {
    $teacher = createTeacherForStatisticsPage();
    $course = Course::factory()->create([
        'execution' => 0,
        'min_stp_sessions' => 2,
    ]);
    $training = createTrainingForStatisticsPage($teacher);
    $training->update(['course_id' => $course->id]);

    $mentor = User::factory()->create(['name' => 'Mentor A']);
    $studentPending = User::factory()->create(['name' => 'Aluno Pendente']);
    $studentComplete = User::factory()->create(['name' => 'Aluno Completo']);

    $training->students()->attach([$studentPending->id, $studentComplete->id]);

    $sessionOne = StpSession::query()->create([
        'training_id' => $training->id,
        'sequence' => 1,
    ]);
    $sessionTwo = StpSession::query()->create([
        'training_id' => $training->id,
        'sequence' => 2,
    ]);

    $teamOne = StpTeam::query()->create([
        'stp_session_id' => $sessionOne->id,
        'mentor_user_id' => $mentor->id,
        'name' => 'Equipe 01',
        'position' => 0,
    ]);
    $teamTwo = StpTeam::query()->create([
        'stp_session_id' => $sessionTwo->id,
        'mentor_user_id' => $mentor->id,
        'name' => 'Equipe 01',
        'position' => 0,
    ]);

    $teamOne->students()->attach($studentPending->id, ['position' => 0]);
    $teamOne->students()->attach($studentComplete->id, ['position' => 1]);
    $teamTwo->students()->attach($studentComplete->id, ['position' => 0]);

    Livewire::actingAs($teacher)
        ->test(Statistics::class, ['training' => $training])
        ->assertSet('pendingStudents', [
            [
                'student_id' => $studentPending->id,
                'name' => 'Aluno Pendente',
                'participated' => 1,
                'missing' => 1,
            ],
        ]);
});

it('removes selected session and deletes teams approaches and pivots in cascade', function () {
    $teacher = createTeacherForStatisticsPage();
    $training = createTrainingForStatisticsPage($teacher);
    $mentor = User::factory()->create();
    $student = User::factory()->create();

    $training->mentors()->attach($mentor->id, ['created_by' => $teacher->id]);
    $training->students()->attach($student->id);

    $session = StpSession::query()->create([
        'training_id' => $training->id,
        'sequence' => 1,
    ]);
    $team = StpTeam::query()->create([
        'stp_session_id' => $session->id,
        'mentor_user_id' => $mentor->id,
        'name' => 'Equipe 01',
        'position' => 0,
    ]);
    $team->students()->attach($student->id, ['position' => 0]);

    $approach = StpApproach::query()->create([
        'training_id' => $training->id,
        'stp_session_id' => $session->id,
        'stp_team_id' => $team->id,
        'type' => StpApproachType::Visitor->value,
        'status' => StpApproachStatus::Assigned->value,
        'position' => 0,
        'person_name' => 'Pessoa',
        'created_by_user_id' => $teacher->id,
    ]);

    Livewire::actingAs($teacher)
        ->test(Statistics::class, ['training' => $training])
        ->call('removeSession', $session->id);

    $this->assertDatabaseMissing('stp_sessions', ['id' => $session->id]);
    $this->assertDatabaseMissing('stp_teams', ['id' => $team->id]);
    $this->assertDatabaseMissing('stp_approaches', ['id' => $approach->id]);
    $this->assertDatabaseMissing('stp_team_students', [
        'stp_team_id' => $team->id,
        'user_id' => $student->id,
    ]);
});
