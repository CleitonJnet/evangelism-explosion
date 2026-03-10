<?php

use App\Livewire\Pages\App\Director\Training\Registrations as DirectorRegistrations;
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
        ->assertSee('Entrega manual')
        ->assertSee('Gerenciamento de inscrições');

    $component = Livewire::actingAs($director)
        ->test(DirectorRegistrations::class, ['training' => $training])
        ->instance();

    expect($component->usesManualMaterialDelivery())->toBeTrue()
        ->and($component->canToggleRegistrationKit())->toBeFalse()
        ->and($component->capabilities['canSeeSensitiveData'])->toBeTrue();
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
