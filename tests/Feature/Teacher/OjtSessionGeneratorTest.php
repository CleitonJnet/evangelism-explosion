<?php

declare(strict_types=1);

use App\Models\OjtReport;
use App\Models\OjtSession;
use App\Models\OjtTeam;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;

function createTeacherUserForOjtSessions(): User
{
    $role = Role::query()->firstOrCreate(['name' => 'Teacher']);
    $user = User::factory()->create();
    $user->roles()->syncWithoutDetaching([$role->id]);

    return $user;
}

it('creates missing ojt sessions based on expected count', function () {
    $teacher = createTeacherUserForOjtSessions();

    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'ojt_count_override' => 2,
    ]);

    OjtSession::query()->where('training_id', $training->id)->delete();

    $response = $this->actingAs($teacher)
        ->post(route('app.teacher.trainings.ojt.sessions.generate', [
            'training' => $training->id,
        ]));

    $response->assertSuccessful()
        ->assertJson([
            'ok' => true,
            'created_count' => 2,
        ]);

    expect(OjtSession::query()->where('training_id', $training->id)->count())->toBe(2);
});

it('cancels extra sessions without reports', function () {
    $teacher = createTeacherUserForOjtSessions();
    $mentor = User::factory()->create();

    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'ojt_count_override' => 1,
    ]);

    $sessionWithReport = OjtSession::query()->create([
        'training_id' => $training->id,
        'date' => now()->toDateString(),
        'week_number' => 1,
        'status' => 'planned',
    ]);

    $sessionWithoutReport = OjtSession::query()->create([
        'training_id' => $training->id,
        'date' => now()->addWeek()->toDateString(),
        'week_number' => 2,
        'status' => 'planned',
    ]);

    $team = OjtTeam::query()->create([
        'ojt_session_id' => $sessionWithReport->id,
        'mentor_id' => $mentor->id,
        'team_number' => 1,
    ]);

    OjtReport::query()->create([
        'ojt_team_id' => $team->id,
    ]);

    $response = $this->actingAs($teacher)
        ->post(route('app.teacher.trainings.ojt.sessions.generate', [
            'training' => $training->id,
        ]));

    $response->assertSuccessful()
        ->assertJson([
            'ok' => true,
            'canceled_count' => 1,
        ]);

    expect($sessionWithReport->fresh()->status)->not->toBe('canceled')
        ->and($sessionWithoutReport->fresh()->status)->toBe('canceled');
});
