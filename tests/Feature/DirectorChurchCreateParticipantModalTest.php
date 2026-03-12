<?php

use App\Livewire\Pages\App\Director\Church\CreateParticipantModal;
use App\Models\Church;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createDirectorForChurchParticipantModal(): User
{
    $director = User::factory()->create();
    $role = Role::query()->firstOrCreate(['name' => 'Director']);
    $director->roles()->syncWithoutDetaching([$role->id]);

    return $director;
}

it('creates a new church participant from the director modal', function (): void {
    $church = Church::factory()->create(['name' => 'Igreja Diretor']);
    $director = createDirectorForChurchParticipantModal();

    Livewire::actingAs($director)
        ->test(CreateParticipantModal::class, ['churchId' => $church->id])
        ->call('openModal', $church->id)
        ->set('email', 'diretor.participante@example.org')
        ->call('identifyByEmail')
        ->set('ispastor', '0')
        ->set('name', 'ana maria')
        ->set('mobile', '21988887777')
        ->set('birth_date', '1995-03-01')
        ->set('gender', '2')
        ->call('registerParticipant')
        ->assertDispatched('director-church-participant-created', churchId: $church->id)
        ->assertSet('showModal', false);

    $participant = User::query()->where('email', 'diretor.participante@example.org')->first();

    expect($participant)->not->toBeNull();
    expect($participant?->name)->toBe('Ana Maria');
    expect($participant?->church_id)->toBe($church->id);
    expect($participant?->must_change_password)->toBeTrue();
});

it('updates an existing participant church from the director modal', function (): void {
    $originChurch = Church::factory()->create();
    $targetChurch = Church::factory()->create();
    $director = createDirectorForChurchParticipantModal();
    $participant = User::factory()->create([
        'church_id' => $originChurch->id,
        'email' => 'diretor.existente@example.org',
        'name' => 'Diretor Existente',
        'gender' => 1,
        'is_pastor' => 0,
    ]);

    Livewire::actingAs($director)
        ->test(CreateParticipantModal::class, ['churchId' => $targetChurch->id])
        ->call('openModal', $targetChurch->id)
        ->set('email', $participant->email)
        ->call('identifyByEmail')
        ->set('ispastor', '1')
        ->call('registerParticipant')
        ->assertDispatched('director-church-participant-created', churchId: $targetChurch->id);

    expect($participant->fresh()->church_id)->toBe($targetChurch->id);
    expect($participant->fresh()->is_pastor)->toBeTrue();
});
