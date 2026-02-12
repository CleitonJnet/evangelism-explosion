<?php

use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('deletes a training', function () {
    $training = Training::factory()->create();
    $user = User::factory()->create();
    $role = Role::query()->create(['name' => 'Director']);

    $user->roles()->attach($role->id);

    $this->actingAs($user)
        ->delete(route('app.director.training.destroy', $training))
        ->assertRedirect(route('app.director.training.index'));

    expect(Training::query()->whereKey($training->id)->exists())->toBeFalse();
});
