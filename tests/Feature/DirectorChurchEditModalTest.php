<?php

use App\Livewire\Pages\App\Director\Church\EditModal;
use App\Models\Church;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createDirectorForChurchEditModal(): User
{
    $director = User::factory()->create();
    $role = Role::query()->firstOrCreate(['name' => 'Director']);

    $director->roles()->syncWithoutDetaching([$role->id]);

    return $director;
}

it('updates church details from director edit modal', function (): void {
    Storage::fake('public');

    $church = Church::factory()->create([
        'name' => 'Igreja Base Diretor',
        'pastor' => 'Pr. Inicial',
        'city' => 'Cidade Inicial',
        'state' => 'SP',
    ]);

    $director = createDirectorForChurchEditModal();
    $newLogo = UploadedFile::fake()->image('logo-diretor.png', 220, 220);

    Livewire::actingAs($director)
        ->test(EditModal::class, ['churchId' => $church->id])
        ->call('openModal', $church->id)
        ->set('logoUpload', $newLogo)
        ->set('church_name', 'Igreja Atualizada Diretor')
        ->set('pastor_name', 'Pr. Atualizado Diretor')
        ->set('phone_church', '11999990000')
        ->set('church_email', 'diretor@example.org')
        ->set('church_contact', 'Contato Diretor')
        ->set('church_contact_phone', '11888880000')
        ->set('church_contact_email', 'contato-diretor@example.org')
        ->set('church_notes', 'Observacao diretor')
        ->set('churchAddress.postal_code', '70000000')
        ->set('churchAddress.street', 'Rua Diretor')
        ->set('churchAddress.number', '321')
        ->set('churchAddress.complement', 'Sala D')
        ->set('churchAddress.district', 'Centro Diretor')
        ->set('churchAddress.city', 'Brasilia')
        ->set('churchAddress.state', 'df')
        ->call('save')
        ->assertDispatched('director-church-updated', churchId: $church->id)
        ->assertSet('showModal', false);

    $updatedChurch = $church->fresh();

    expect($updatedChurch->name)->toBe('Igreja Atualizada Diretor');
    expect($updatedChurch->pastor)->toBe('Pr. Atualizado Diretor');
    expect($updatedChurch->city)->toBe('Brasilia');
    expect($updatedChurch->getRawOriginal('state'))->toBe('DF');
    expect($updatedChurch->logo)->not->toBeNull();

    Storage::disk('public')->assertExists((string) $updatedChurch->logo);
});

it('allows director to save edit modal with optional fields empty', function (): void {
    $church = Church::factory()->create([
        'name' => 'Igreja Inicial Diretor',
        'pastor' => 'Pr. Inicial Diretor',
        'phone' => '11999990000',
        'contact' => 'Contato Inicial Diretor',
        'contact_phone' => '11888880000',
        'city' => 'Cidade Inicial',
        'state' => 'SP',
    ]);

    $director = createDirectorForChurchEditModal();

    Livewire::actingAs($director)
        ->test(EditModal::class, ['churchId' => $church->id])
        ->call('openModal', $church->id)
        ->set('church_name', '')
        ->set('pastor_name', '')
        ->set('phone_church', '')
        ->set('church_email', null)
        ->set('church_contact', '')
        ->set('church_contact_phone', '')
        ->set('church_contact_email', null)
        ->set('church_notes', null)
        ->set('churchAddress.postal_code', '')
        ->set('churchAddress.street', '')
        ->set('churchAddress.number', '')
        ->set('churchAddress.complement', '')
        ->set('churchAddress.district', '')
        ->set('churchAddress.city', '')
        ->set('churchAddress.state', '')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('showModal', false);

    $updatedChurch = $church->fresh();

    expect($updatedChurch->name)->toBe('');
    expect($updatedChurch->pastor)->toBe('');
    expect($updatedChurch->contact)->toBe('');
    expect($updatedChurch->city)->toBe('');
    expect($updatedChurch->state)->toBeNull();
});
