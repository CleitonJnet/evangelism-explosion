<?php

use App\Livewire\Pages\App\Director\Church\View;
use App\Models\Church;
use App\Models\User;
use Livewire\Livewire;

test('church view shows profile links with correct parameters', function () {
    $church = Church::create(['name' => 'Igreja Central']);
    $profile = User::factory()->create(['name' => 'Perfil Um']);

    Livewire::test(View::class, ['church' => $church])
        ->assertSee(route('app.director.church.profile.show', [
            'id' => $church->id,
            'profile' => $profile->id,
        ]))
        ->assertSee('Perfil Um');
});
test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
