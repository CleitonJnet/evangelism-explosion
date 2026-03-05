<?php

use App\Models\Church;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

function createUserWithRoles(string ...$roles): User
{
    $user = User::factory()->create();

    $roleIds = collect($roles)
        ->map(fn (string $role): int => (int) Role::query()->firstOrCreate(['name' => $role])->id)
        ->all();

    $user->roles()->syncWithoutDetaching($roleIds);

    return $user;
}

it('allows director with teacher role to access any church', function (): void {
    $church = Church::factory()->create();
    $user = createUserWithRoles('Director', 'Teacher');

    expect(Gate::forUser($user)->allows('view', $church))->toBeTrue();
    expect(Gate::forUser($user)->allows('update', $church))->toBeTrue();
    expect(Gate::forUser($user)->allows('delete', $church))->toBeTrue();
});

it('allows director church show route even when user also has teacher role', function (): void {
    $church = Church::factory()->create();
    $user = createUserWithRoles('Director', 'Teacher');

    $this->actingAs($user)
        ->get(route('app.director.church.show', $church))
        ->assertOk();
});
