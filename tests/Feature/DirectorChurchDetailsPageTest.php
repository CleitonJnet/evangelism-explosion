<?php

use App\Livewire\Pages\App\Director\Church\View as ChurchDetailsView;
use App\Models\Church;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createDirectorForChurchDetails(): User
{
    $director = User::factory()->create();
    $directorRole = Role::query()->firstOrCreate(['name' => 'Director']);
    $director->roles()->syncWithoutDetaching([$directorRole->id]);

    return $director;
}

it('refreshes director church details when church is updated via modal event', function (): void {
    $church = Church::factory()->create([
        'name' => 'Igreja Diretor Original',
        'pastor' => 'Pr. Diretor Original',
    ]);

    $director = createDirectorForChurchDetails();

    Livewire::actingAs($director)
        ->test(ChurchDetailsView::class, ['church' => $church])
        ->assertSee('Igreja Diretor Original')
        ->assertSee('Pr. Diretor Original');

    $church->update([
        'name' => 'Igreja Diretor Atualizada',
        'pastor' => 'Pr. Diretor Atualizado',
    ]);

    Livewire::actingAs($director)
        ->test(ChurchDetailsView::class, ['church' => $church])
        ->dispatch('director-church-updated', churchId: $church->id)
        ->assertSee('Igreja Diretor Atualizada')
        ->assertSee('Pr. Diretor Atualizado');
});
