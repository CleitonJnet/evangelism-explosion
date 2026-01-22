<?php

use App\Models\User;
use Livewire\Volt\Volt;

it('renders the student training index for authenticated users', function () {
    $user = User::factory()->create();

    Volt::test('pages.app.student.training.index')
        ->actingAs($user)
        ->assertSee('Meus treinamentos')
        ->assertSee('Voce ainda nao se inscreveu em nenhum treinamento.');
});
