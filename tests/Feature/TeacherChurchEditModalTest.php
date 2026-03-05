<?php

use App\Livewire\Pages\App\Teacher\Church\EditModal;
use App\Models\Church;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createTeacherForChurchEditModal(): User
{
    $teacher = User::factory()->create();
    $role = Role::query()->firstOrCreate(['name' => 'Teacher']);

    $teacher->roles()->syncWithoutDetaching([$role->id]);

    return $teacher;
}

it('allows teacher to save edit modal with optional fields empty', function (): void {
    $church = Church::factory()->create([
        'name' => 'Igreja Inicial Professor',
        'pastor' => 'Pr. Inicial Professor',
        'phone' => '11999990000',
        'contact' => 'Contato Inicial Professor',
        'contact_phone' => '11888880000',
        'city' => 'Cidade Inicial',
        'state' => 'SP',
    ]);

    $teacher = createTeacherForChurchEditModal();
    $church->missionaries()->syncWithoutDetaching([$teacher->id]);

    Livewire::actingAs($teacher)
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
