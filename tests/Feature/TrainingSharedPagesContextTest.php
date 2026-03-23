<?php

use App\Livewire\Pages\App\Director\Training\Registrations as DirectorRegistrations;
use App\Livewire\Pages\App\Teacher\Training\CreateParticipantRegistrationModal;
use App\Livewire\Pages\App\Teacher\Training\Statistics as TeacherStatistics;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function userWithTrainingRole(string $roleName): User
{
    $user = User::factory()->create();
    $role = Role::query()->firstOrCreate(['name' => $roleName]);
    $user->roles()->syncWithoutDetaching([$role->id]);

    return $user;
}

it('renders the teacher statistics page through the old route with shared capabilities', function (): void {
    $teacher = userWithTrainingRole('Teacher');
    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
    ]);

    $this->actingAs($teacher)
        ->get(route('app.teacher.trainings.statistics', $training))
        ->assertOk()
        ->assertSee('Saidas de Treinamento Praticos')
        ->assertSee('Visitas');

    Livewire::actingAs($teacher)
        ->test(TeacherStatistics::class, ['training' => $training])
        ->assertSet('capabilities.canManageMentors', true)
        ->assertSet('capabilities.canManageSchedule', true)
        ->assertSet('capabilities.canSeeDiscipleship', true);
});

it('renders the director registrations page through the old route with director context actions', function (): void {
    $director = userWithTrainingRole('Director');
    $training = Training::factory()->create();

    $this->actingAs($director)
        ->get(route('app.director.training.registrations', $training))
        ->assertOk()
        ->assertSee('Gerenciamento de inscrições')
        ->assertDontSee('Entrega manual');

    $component = Livewire::actingAs($director)
        ->test(DirectorRegistrations::class, ['training' => $training])
        ->instance();

    expect($component->usesManualMaterialDelivery())->toBeFalse()
        ->and($component->canToggleRegistrationKit())->toBeTrue()
        ->and($component->capabilities['canSeeSensitiveData'])->toBeTrue();
});

it('allows the director to mark kit delivery from the registrations list without using stock delivery flow', function (): void {
    $director = userWithTrainingRole('Director');
    $training = Training::factory()->create();
    $participant = User::factory()->create();

    $training->students()->attach($participant->id, [
        'kit' => 0,
        'payment' => 0,
        'accredited' => 0,
    ]);

    Livewire::actingAs($director)
        ->test(DirectorRegistrations::class, ['training' => $training])
        ->call('toggleKit', $participant->id, true);

    $this->assertDatabaseHas('training_user', [
        'training_id' => $training->id,
        'user_id' => $participant->id,
        'kit' => 1,
    ]);
});

it('allows directors to open the participant registration modal for trainings owned by another teacher', function (): void {
    $director = userWithTrainingRole('Director');
    $teacher = userWithTrainingRole('Teacher');
    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
    ]);

    Livewire::actingAs($director)
        ->test(CreateParticipantRegistrationModal::class, ['trainingId' => $training->id])
        ->call('openModal', $training->id)
        ->assertSet('showModal', true);
});

it('keeps mentor users out of teacher and director training routes even when they are assigned to the training', function (): void {
    $mentor = userWithTrainingRole('Mentor');
    $training = Training::factory()->create();
    $training->mentors()->attach($mentor->id, ['created_by' => User::factory()->create()->id]);

    $this->actingAs($mentor)
        ->get(route('app.teacher.trainings.statistics', $training))
        ->assertForbidden();

    $this->actingAs($mentor)
        ->get(route('app.director.training.registrations', $training))
        ->assertForbidden();
});
