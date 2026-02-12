<?php

declare(strict_types=1);

use App\Models\OjtSession;
use App\Models\OjtTeam;
use App\Models\OjtTrainingMentor;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;

function createTeacherUserForOjtTeams(): User
{
    $role = Role::query()->firstOrCreate(['name' => 'Teacher']);
    $user = User::factory()->create();
    $user->roles()->syncWithoutDetaching([$role->id]);

    return $user;
}

function seedOjtSessions(Training $training, int $count): array
{
    $sessions = [];

    for ($i = 1; $i <= $count; $i++) {
        $sessions[] = OjtSession::query()->create([
            'training_id' => $training->id,
            'date' => now()->addWeeks($i - 1)->toDateString(),
            'week_number' => $i,
            'status' => 'planned',
        ]);
    }

    return $sessions;
}

it('generates fixed teams using the first session as template', function () {
    $teacher = createTeacherUserForOjtTeams();

    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'ojt_policy_override' => Training::OJT_POLICY_FIXED,
    ]);

    $sessions = seedOjtSessions($training, 2);

    $mentors = User::factory()->count(2)->create();
    foreach ($mentors as $mentor) {
        OjtTrainingMentor::query()->create([
            'training_id' => $training->id,
            'mentor_id' => $mentor->id,
            'status' => 'active',
        ]);
    }

    $trainees = User::factory()->count(4)->create();
    $training->students()->sync($trainees->pluck('id')->all());

    $response = $this->actingAs($teacher)
        ->post(route('app.teacher.trainings.ojt.teams.generate', [
            'training' => $training->id,
        ]));

    $response->assertSuccessful()
        ->assertJson([
            'ok' => true,
            'created_count' => 4,
        ]);

    $firstTeams = OjtTeam::query()
        ->where('ojt_session_id', $sessions[0]->id)
        ->with('trainees')
        ->orderBy('team_number')
        ->get();

    $secondTeams = OjtTeam::query()
        ->where('ojt_session_id', $sessions[1]->id)
        ->with('trainees')
        ->orderBy('team_number')
        ->get();

    $firstSignature = $firstTeams->map(fn (OjtTeam $team) => [
        'mentor_id' => $team->mentor_id,
        'trainees' => $team->trainees->sortBy('order')->pluck('trainee_id')->values()->all(),
    ])->all();

    $secondSignature = $secondTeams->map(fn (OjtTeam $team) => [
        'mentor_id' => $team->mentor_id,
        'trainees' => $team->trainees->sortBy('order')->pluck('trainee_id')->values()->all(),
    ])->all();

    expect($secondSignature)->toBe($firstSignature);
});

it('rotates mentor assignments across sessions when possible', function () {
    $teacher = createTeacherUserForOjtTeams();

    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'ojt_policy_override' => Training::OJT_POLICY_ROTATE,
    ]);

    $sessions = seedOjtSessions($training, 2);

    $mentors = User::factory()->count(2)->create();
    foreach ($mentors as $mentor) {
        OjtTrainingMentor::query()->create([
            'training_id' => $training->id,
            'mentor_id' => $mentor->id,
            'status' => 'active',
        ]);
    }

    $trainees = User::factory()->count(4)->create();
    $training->students()->sync($trainees->pluck('id')->all());

    $response = $this->actingAs($teacher)
        ->post(route('app.teacher.trainings.ojt.teams.generate', [
            'training' => $training->id,
        ]));

    $response->assertSuccessful();

    $sessionOneMap = [];
    $sessionOneTeams = OjtTeam::query()
        ->where('ojt_session_id', $sessions[0]->id)
        ->with('trainees')
        ->get();

    foreach ($sessionOneTeams as $team) {
        foreach ($team->trainees as $trainee) {
            $sessionOneMap[$trainee->trainee_id] = $team->mentor_id;
        }
    }

    $sessionTwoMap = [];
    $sessionTwoTeams = OjtTeam::query()
        ->where('ojt_session_id', $sessions[1]->id)
        ->with('trainees')
        ->get();

    foreach ($sessionTwoTeams as $team) {
        foreach ($team->trainees as $trainee) {
            $sessionTwoMap[$trainee->trainee_id] = $team->mentor_id;
        }
    }

    foreach ($trainees as $trainee) {
        expect($sessionOneMap[$trainee->id])->not->toBe($sessionTwoMap[$trainee->id]);
    }
});
