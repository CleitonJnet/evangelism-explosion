<?php

use App\Livewire\Pages\App\Teacher\Training\CreateChurchModal;
use App\Models\Church;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('creates an official church and selects it immediately in create training flow', function (): void {
    $teacher = User::factory()->create();
    $course = Course::factory()->create();

    Livewire::actingAs($teacher)
        ->test(CreateChurchModal::class, [
            'trainingCourseId' => $course->id,
            'trainingTeacherId' => $teacher->id,
        ])
        ->set('church_name', 'Igreja Novo Tempo')
        ->set('pastor_name', 'Pr. Marcos Lima')
        ->set('phone_church', '61999998888')
        ->set('church_email', 'igreja.novo.tempo@example.org')
        ->set('church_contact', 'Maria Souza')
        ->set('church_contact_phone', '61988887777')
        ->set('church_contact_email', 'maria@example.org')
        ->set('churchAddress.postal_code', '70000000')
        ->set('churchAddress.street', 'Rua Principal')
        ->set('churchAddress.number', '100')
        ->set('churchAddress.district', 'Centro')
        ->set('churchAddress.city', 'Brasilia')
        ->set('churchAddress.state', 'df')
        ->call('save')
        ->assertSet('selectedChurch.name', 'Igreja Novo Tempo')
        ->assertDispatched('church-created');

    $createdChurch = Church::query()->where('name', 'Igreja Novo Tempo')->first();

    expect($createdChurch)->not->toBeNull();
    expect($createdChurch->pastor)->toBe('Pr. Marcos Lima');
    expect($createdChurch->getRawOriginal('state'))->toBe('DF');
});
