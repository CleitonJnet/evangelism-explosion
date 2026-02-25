<?php

use App\Livewire\Pages\App\Teacher\Training\EditEventChurchModal;
use App\Models\Church;
use App\Models\Course;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use Livewire\Livewire;

function createTeacherForEventChurchModal(): User
{
    $teacher = User::factory()->create(['church_id' => null]);
    $teacherRole = Role::query()->firstOrCreate(['name' => 'Teacher']);
    $teacher->roles()->syncWithoutDetaching([$teacherRole->id]);

    return $teacher;
}

function createTrainingForEventChurchModal(User $teacher): Training
{
    $course = Course::factory()->create();
    $hostChurch = Church::factory()->create();

    return Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
        'church_id' => $hostChurch->id,
    ]);
}

it('loads current training data as default in church edit modal', function (): void {
    $teacher = createTeacherForEventChurchModal();
    $training = createTrainingForEventChurchModal($teacher);

    $training->update([
        'leader' => 'Pr. Elias Lima',
        'coordinator' => 'Marina Alves',
        'street' => 'Rua Atual',
        'number' => '120',
        'complement' => 'Anexo',
        'district' => 'Centro',
        'city' => 'Fortaleza',
        'state' => 'CE',
        'postal_code' => '60000000',
    ]);

    Livewire::actingAs($teacher)
        ->test(EditEventChurchModal::class, ['trainingId' => $training->id])
        ->call('openModal', $training->id)
        ->assertSet('church_id', $training->church_id)
        ->assertSet('leader', 'Pr. Elias Lima')
        ->assertSet('coordinator', 'Marina Alves')
        ->assertSet('address.street', 'Rua Atual')
        ->assertSet('address.number', '120')
        ->assertSet('address.complement', 'Anexo')
        ->assertSet('address.district', 'Centro')
        ->assertSet('address.city', 'Fortaleza')
        ->assertSet('address.state', 'CE')
        ->assertSet('address.postal_code', '60.000-000');
});

it('applies church defaults when selecting host church with fallback for coordinator', function (): void {
    $teacher = createTeacherForEventChurchModal();
    $training = createTrainingForEventChurchModal($teacher);
    $church = Church::factory()->create([
        'pastor' => 'Pr. Josue Ribeiro',
        'contact' => null,
        'street' => 'Rua da Esperanca',
        'number' => '300',
        'complement' => 'Sala 2',
        'district' => 'Nova Vida',
        'city' => 'Goiania',
        'state' => 'GO',
        'postal_code' => '74000000',
    ]);

    Livewire::actingAs($teacher)
        ->test(EditEventChurchModal::class, ['trainingId' => $training->id])
        ->call('openModal', $training->id)
        ->call('selectChurch', $church->id)
        ->assertSet('church_id', $church->id)
        ->assertSet('leader', 'Pr. Josue Ribeiro')
        ->assertSet('coordinator', 'Pr. Josue Ribeiro')
        ->assertSet('address.street', 'Rua da Esperanca')
        ->assertSet('address.number', '300')
        ->assertSet('address.complement', 'Sala 2')
        ->assertSet('address.district', 'Nova Vida')
        ->assertSet('address.city', 'Goiania')
        ->assertSet('address.state', 'GO')
        ->assertSet('address.postal_code', '74.000-000');
});

it('updates training church leader coordinator and address from modal', function (): void {
    $teacher = createTeacherForEventChurchModal();
    $training = createTrainingForEventChurchModal($teacher);
    $church = Church::factory()->create();

    Livewire::actingAs($teacher)
        ->test(EditEventChurchModal::class, ['trainingId' => $training->id])
        ->call('openModal', $training->id)
        ->set('church_id', $church->id)
        ->set('leader', 'Pr. Daniel Freitas')
        ->set('coordinator', 'Luciana Costa')
        ->set('address.street', 'Rua Nova Jerusalem')
        ->set('address.number', '55')
        ->set('address.complement', 'Fundos')
        ->set('address.district', 'Setor Sul')
        ->set('address.city', 'Brasilia')
        ->set('address.state', 'df')
        ->set('address.postal_code', '70000000')
        ->call('save')
        ->assertSet('showModal', false)
        ->assertDispatched('training-church-updated', trainingId: $training->id);

    $training->refresh();

    expect($training->church_id)->toBe($church->id)
        ->and($training->leader)->toBe('Pr. Daniel Freitas')
        ->and($training->coordinator)->toBe('Luciana Costa')
        ->and($training->street)->toBe('Rua Nova Jerusalem')
        ->and($training->number)->toBe('55')
        ->and($training->complement)->toBe('Fundos')
        ->and($training->district)->toBe('Setor Sul')
        ->and($training->city)->toBe('Brasilia')
        ->and($training->state)->toBe('DF')
        ->and($training->getRawOriginal('postal_code'))->toBe('70000000');
});

it('forbids teacher that does not own the training when editing church modal', function (): void {
    $ownerTeacher = createTeacherForEventChurchModal();
    $otherTeacher = createTeacherForEventChurchModal();
    $training = createTrainingForEventChurchModal($ownerTeacher);

    Livewire::actingAs($otherTeacher)
        ->test(EditEventChurchModal::class, ['trainingId' => $training->id])
        ->assertForbidden();
});

it('shows the church edit button in teacher training details toolbar', function (): void {
    $teacher = createTeacherForEventChurchModal();
    $training = createTrainingForEventChurchModal($teacher);

    $this->actingAs($teacher)
        ->get(route('app.teacher.trainings.show', $training))
        ->assertOk()
        ->assertSee('Igreja Sede');
});

it('does not show the church edit button in teacher schedule toolbar', function (): void {
    $teacher = createTeacherForEventChurchModal();
    $training = createTrainingForEventChurchModal($teacher);

    $this->actingAs($teacher)
        ->get(route('app.teacher.trainings.schedule', $training))
        ->assertOk()
        ->assertDontSee('Igreja Sede');
});
