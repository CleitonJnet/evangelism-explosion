<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureChurchLinked;
use App\Models\OjtSession;
use App\Models\OjtTeam;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;

function createMentorUser(): User
{
    $role = Role::query()->firstOrCreate(['name' => 'Mentor']);
    $user = User::factory()->create();
    $user->roles()->syncWithoutDetaching([$role->id]);

    return $user;
}

it('allows mentor to access assigned ojt session and report', function () {
    $mentor = createMentorUser();

    $training = Training::factory()->create();
    $session = OjtSession::query()->create([
        'training_id' => $training->id,
        'date' => now()->toDateString(),
        'week_number' => 1,
        'status' => 'planned',
    ]);

    $team = OjtTeam::query()->create([
        'ojt_session_id' => $session->id,
        'mentor_id' => $mentor->id,
        'team_number' => 1,
    ]);

    $this->withoutMiddleware([EnsureChurchLinked::class]);

    $this->actingAs($mentor)
        ->get(route('app.mentor.ojt.sessions.show', $session))
        ->assertSuccessful()
        ->assertSee('OJT Session');

    $this->actingAs($mentor)
        ->get(route('app.mentor.ojt.teams.report.create', $team))
        ->assertSuccessful()
        ->assertSee('OJT Report');
});

it('forbids mentor from accessing unassigned ojt session', function () {
    $mentor = createMentorUser();
    $otherMentor = createMentorUser();

    $training = Training::factory()->create();
    $session = OjtSession::query()->create([
        'training_id' => $training->id,
        'date' => now()->toDateString(),
        'week_number' => 1,
        'status' => 'planned',
    ]);

    $team = OjtTeam::query()->create([
        'ojt_session_id' => $session->id,
        'mentor_id' => $otherMentor->id,
        'team_number' => 1,
    ]);

    $this->withoutMiddleware([EnsureChurchLinked::class]);

    $this->actingAs($mentor)
        ->get(route('app.mentor.ojt.sessions.show', $session))
        ->assertForbidden();

    $this->actingAs($mentor)
        ->get(route('app.mentor.ojt.teams.report.create', $team))
        ->assertForbidden();
});
