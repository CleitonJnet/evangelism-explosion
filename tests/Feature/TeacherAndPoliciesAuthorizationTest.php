<?php

use App\Models\Role;
use App\Models\StpApproach;
use App\Models\StpSession;
use App\Models\StpTeam;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

function userWithRole(string $role): User
{
    $user = User::factory()->create();
    $roleModel = Role::query()->firstOrCreate(['name' => $role]);
    $user->roles()->syncWithoutDetaching([$roleModel->id]);

    return $user;
}

it('allows access-teacher gate only for users with Teacher role', function (string $role, bool $expected): void {
    $user = userWithRole($role);

    expect(Gate::forUser($user)->allows('access-teacher'))->toBe($expected);
})->with([
    'teacher allowed' => ['Teacher', true],
    'director denied' => ['Director', false],
    'mentor denied' => ['Mentor', false],
]);

it('allows training policy for owner teacher and director and denies other teacher', function (): void {
    $ownerTeacher = userWithRole('Teacher');
    $otherTeacher = userWithRole('Teacher');
    $director = userWithRole('Director');

    $training = Training::factory()->create([
        'teacher_id' => $ownerTeacher->id,
    ]);

    foreach (['view', 'update', 'delete'] as $ability) {
        expect(Gate::forUser($ownerTeacher)->allows($ability, $training))->toBeTrue();
        expect(Gate::forUser($director)->allows($ability, $training))->toBeTrue();
        expect(Gate::forUser($otherTeacher)->allows($ability, $training))->toBeFalse();
    }
});

it('allows stp approach policy for training teacher and team mentor and denies unrelated user', function (): void {
    $teacher = User::factory()->create();
    $mentor = User::factory()->create();
    $outsider = User::factory()->create();

    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
    ]);

    $session = StpSession::factory()->create([
        'training_id' => $training->id,
    ]);

    $team = StpTeam::factory()->create([
        'stp_session_id' => $session->id,
        'mentor_user_id' => $mentor->id,
    ]);

    $approach = StpApproach::factory()->create([
        'training_id' => $training->id,
        'stp_session_id' => $session->id,
        'stp_team_id' => $team->id,
    ]);

    expect(Gate::forUser($teacher)->allows('view', $approach))->toBeTrue();
    expect(Gate::forUser($teacher)->allows('update', $approach))->toBeTrue();

    expect(Gate::forUser($mentor)->allows('view', $approach))->toBeTrue();
    expect(Gate::forUser($mentor)->allows('update', $approach))->toBeTrue();

    expect(Gate::forUser($outsider)->allows('view', $approach))->toBeFalse();
    expect(Gate::forUser($outsider)->allows('update', $approach))->toBeFalse();
});
