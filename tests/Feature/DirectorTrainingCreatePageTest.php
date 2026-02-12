<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the training create header toolbar', function () {
    $user = User::factory()->create();
    $role = Role::query()->create(['name' => 'Director']);

    $user->roles()->attach($role->id);

    $this->actingAs($user)
        ->get(route('app.director.training.create'))
        ->assertSuccessful()
        ->assertSee('EVANGELISMO EXPLOSIVO')
        ->assertSee('Novo treinamento')
        ->assertSee('Listar todos');
});
