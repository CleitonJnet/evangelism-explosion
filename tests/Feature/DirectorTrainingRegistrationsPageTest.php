<?php

use App\Models\Church;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createDirectorForRegistrationsPageTest(): User
{
    $director = User::factory()->create();
    $directorRole = Role::query()->firstOrCreate(['name' => 'Director']);
    $director->roles()->syncWithoutDetaching([$directorRole->id]);

    return $director;
}

it('does not show the inventories shortcut on the director registrations page', function (): void {
    $director = createDirectorForRegistrationsPageTest();
    $church = Church::factory()->create();
    $training = Training::factory()->create([
        'church_id' => $church->id,
    ]);

    $response = $this->actingAs($director)
        ->get(route('app.director.training.registrations', $training));

    $response->assertOk();
    $response->assertDontSeeText('Estoques');
    $response->assertSeeText('Novo inscrito');
});
