<?php

use App\Livewire\Pages\App\Teacher\Church\CreateParticipantModal;
use App\Models\Church;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createTeacherForChurchParticipantModal(): User
{
    $teacher = User::factory()->create(['church_id' => null]);
    $role = Role::query()->firstOrCreate(['name' => 'Teacher']);
    $teacher->roles()->syncWithoutDetaching([$role->id]);

    return $teacher;
}

it('creates a new church participant from the teacher modal', function (): void {
    $church = Church::factory()->create(['name' => 'Igreja Central']);
    $teacher = createTeacherForChurchParticipantModal();

    Training::factory()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
    ]);

    Livewire::actingAs($teacher)
        ->test(CreateParticipantModal::class, ['churchId' => $church->id])
        ->call('openModal', $church->id)
        ->set('email', 'novo.participante@example.org')
        ->call('identifyByEmail')
        ->set('ispastor', '1')
        ->set('name', '  joao   da silva ')
        ->set('mobile', '11999998888')
        ->set('birth_date', '1990-05-10')
        ->set('gender', '1')
        ->call('registerParticipant')
        ->assertDispatched('teacher-church-participant-created', churchId: $church->id)
        ->assertSet('showModal', false);

    $participant = User::query()->where('email', 'novo.participante@example.org')->first();

    expect($participant)->not->toBeNull();
    expect($participant?->name)->toBe('Joao da Silva');
    expect($participant?->church_id)->toBe($church->id);
    expect($participant?->must_change_password)->toBeTrue();
    expect($participant?->gender)->toBe(1);
    expect($participant?->is_pastor)->toBeTrue();
});

it('updates an existing participant church from the teacher modal', function (): void {
    $originChurch = Church::factory()->create();
    $targetChurch = Church::factory()->create(['name' => 'Igreja Destino']);
    $teacher = createTeacherForChurchParticipantModal();

    Training::factory()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $targetChurch->id,
    ]);

    $participant = User::factory()->create([
        'church_id' => $originChurch->id,
        'email' => 'membro.existente@example.org',
        'name' => 'Maria Existente',
        'phone' => '11911112222',
        'gender' => 2,
        'is_pastor' => 0,
    ]);

    Livewire::actingAs($teacher)
        ->test(CreateParticipantModal::class, ['churchId' => $targetChurch->id])
        ->call('openModal', $targetChurch->id)
        ->set('email', $participant->email)
        ->call('identifyByEmail')
        ->set('ispastor', '1')
        ->set('gender', '2')
        ->call('registerParticipant')
        ->assertDispatched('teacher-church-participant-created', churchId: $targetChurch->id);

    expect($participant->fresh()->church_id)->toBe($targetChurch->id);
    expect($participant->fresh()->is_pastor)->toBeTrue();
});
