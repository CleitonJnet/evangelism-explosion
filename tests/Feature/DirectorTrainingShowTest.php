<?php

use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the training header toolbar', function () {
    $training = Training::factory()->create();
    $user = User::factory()->create();
    $role = Role::query()->create(['name' => 'Director']);

    $user->roles()->attach($role->id);

    $this->actingAs($user)
        ->get(route('app.director.training.show', $training))
        ->assertSuccessful()
        ->assertSee('Detalhes do treinamento')
        ->assertSee('Listar todos')
        ->assertSee('Editar')
        ->assertSee('Excluir');
});
