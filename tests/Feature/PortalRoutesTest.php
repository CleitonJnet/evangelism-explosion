<?php

use App\Models\Role;
use App\Models\User;

function portalUserWithRoles(array $roleNames): User
{
    $user = User::factory()->create();
    $roleIds = collect($roleNames)
        ->map(fn (string $roleName): int => Role::query()->firstOrCreate(['name' => $roleName])->id);

    $user->roles()->syncWithoutDetaching($roleIds->all());

    return $user;
}

it('allows the base portal for operational roles without removing legacy routes', function () {
    $user = portalUserWithRoles(['Teacher']);

    $this->actingAs($user)
        ->get(route('app.portal.base.dashboard'))
        ->assertSuccessful()
        ->assertSee('Portal Base e Treinamentos')
        ->assertSee(route('app.teacher.dashboard'), false);
});

it('allows the staff portal for governance roles', function () {
    $user = portalUserWithRoles(['Director']);

    $this->actingAs($user)
        ->get(route('app.portal.staff.dashboard'))
        ->assertSuccessful()
        ->assertSee('Portal Staff / Governanca');
});

it('allows the student portal for student roles', function () {
    $user = portalUserWithRoles(['Student']);

    $this->actingAs($user)
        ->get(route('app.portal.student.dashboard'))
        ->assertSuccessful()
        ->assertSee('Portal Aluno');
});

it('forbids portal access when the user lacks the required mapped role', function () {
    $user = portalUserWithRoles(['Student']);

    $this->actingAs($user)
        ->get(route('app.portal.staff.dashboard'))
        ->assertForbidden();
});
