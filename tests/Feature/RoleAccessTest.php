<?php

use App\Models\Role;
use App\Models\User;
use Livewire\Volt\Volt;

test('director role can access the director dashboard', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'Director']);

    $user->roles()->attach($role);

    $this->actingAs($user)
        ->get(route('app.director.dashboard'))
        ->assertOk();
});

test('board role can access the board dashboard', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'Board']);

    $user->roles()->attach($role);

    $this->actingAs($user)
        ->get(route('app.board.dashboard'))
        ->assertOk();
});

test('teacher role is blocked from the director dashboard', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'Teacher']);

    $user->roles()->attach($role);

    $this->actingAs($user)
        ->get(route('app.director.dashboard'))
        ->assertForbidden();
});

test('fieldworker role can access the fieldworker dashboard', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'FieldWorker']);

    $user->roles()->attach($role);

    $this->actingAs($user)
        ->get(route('app.fieldworker.dashboard'))
        ->assertOk();
});

test('director role can access the setup page', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'Director']);

    $user->roles()->attach($role);

    $this->actingAs($user)
        ->get(route('app.director.setup'))
        ->assertOk();
});

test('teacher role is blocked from the setup page', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'Teacher']);

    $user->roles()->attach($role);

    $this->actingAs($user)
        ->get(route('app.director.setup'))
        ->assertForbidden();
});

test('director can update user roles from setup', function () {
    $director = User::factory()->create();
    $directorRole = Role::create(['name' => 'Director']);
    $teacherRole = Role::create(['name' => 'Teacher']);
    $targetUser = User::factory()->create();

    $director->roles()->attach($directorRole);

    $this->actingAs($director);

    Volt::test('director.setup')
        ->set('selectedUserId', $targetUser->id)
        ->set('selectedRoleIds', [$teacherRole->id])
        ->assertHasNoErrors();

    expect($targetUser->roles()->where('roles.id', $teacherRole->id)->exists())->toBeTrue();
});

test('setup auto-selects first user when searching', function () {
    $director = User::factory()->create();
    $directorRole = Role::create(['name' => 'Director']);
    $targetUser = User::factory()->create(['name' => 'Maria Silva']);

    $director->roles()->attach($directorRole);

    $this->actingAs($director);

    Volt::test('director.setup')
        ->set('search', 'Maria')
        ->assertSet('selectedUserId', $targetUser->id);
});

test('start page shows access buttons only for allowed roles', function () {
    $user = User::factory()->create();
    $mentorRole = Role::create(['name' => 'Mentor']);
    $teacherRole = Role::create(['name' => 'Teacher']);

    $user->roles()->attach([$mentorRole->id, $teacherRole->id]);

    $this->actingAs($user)
        ->get(route('app.start'))
        ->assertOk()
        ->assertSee('data-test="back-to-site"', false)
        ->assertSee('data-test="logout"', false)
        ->assertDontSee('data-test="role-card-board"', false)
        ->assertDontSee('data-test="role-card-director"', false)
        ->assertDontSee('data-test="role-card-fieldworker"', false)
        ->assertSee('data-test="role-card-teacher"', false)
        ->assertSee('data-test="role-card-facilitator"', false)
        ->assertSee('data-test="role-card-mentor"', false)
        ->assertSee('data-test="role-card-student"', false)
        ->assertSee('data-test="role-access-mentor"', false)
        ->assertDontSee('data-test="role-access-student"', false);
});

test('single-role user is redirected from start to their dashboard', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'Director']);

    $user->roles()->attach($role);

    $this->actingAs($user)
        ->get(route('app.start'))
        ->assertRedirect(route('app.director.dashboard'));
});

test('director sidebar renders director menu items', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'Director']);

    $user->roles()->attach($role);

    $this->actingAs($user)
        ->get(route('app.director.dashboard'))
        ->assertOk()
        ->assertSee('Churches');
});

test('teacher sidebar hides director menu items', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'Teacher']);

    $user->roles()->attach($role);

    $this->actingAs($user)
        ->get(route('app.teacher.dashboard'))
        ->assertOk()
        ->assertDontSee('Churches');
});
