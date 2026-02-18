<?php

use App\Livewire\Pages\App\Teacher\Training\EditFinanceModal;
use App\Livewire\Pages\App\Teacher\Training\View as TrainingView;
use App\Models\Church;
use App\Models\Course;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use Livewire\Livewire;

function createTeacherForFinanceModal(): User
{
    $teacher = User::factory()->create(['church_id' => null]);
    $teacherRole = Role::query()->firstOrCreate(['name' => 'Teacher']);
    $teacher->roles()->syncWithoutDetaching([$teacherRole->id]);

    return $teacher;
}

function createTrainingForFinanceModal(User $teacher): Training
{
    $course = Course::factory()->create();
    $hostChurch = Church::factory()->create();

    return Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
        'church_id' => $hostChurch->id,
        'price' => '100,00',
        'price_church' => '0,00',
        'discount' => '0,00',
    ]);
}

it('updates editable finance fields without changing base price', function (): void {
    $teacher = createTeacherForFinanceModal();
    $training = createTrainingForFinanceModal($teacher);

    Livewire::actingAs($teacher)
        ->test(EditFinanceModal::class, ['trainingId' => $training->id])
        ->call('openModal', $training->id)
        ->set('price_church', '25,00')
        ->set('discount', '5,00')
        ->set('pix_key', 'church-pix-key@example.test')
        ->call('save')
        ->assertSet('showModal', false)
        ->assertDispatched('training-finance-updated', trainingId: $training->id);

    $training->refresh();

    expect($training->getRawOriginal('price'))->toBe('100,00')
        ->and($training->getRawOriginal('price_church'))->toBe('25,00')
        ->and($training->getRawOriginal('discount'))->toBe('5,00')
        ->and($training->pix_key)->toBe('church-pix-key@example.test');
});

it('forbids teacher that does not own the training', function (): void {
    $ownerTeacher = createTeacherForFinanceModal();
    $otherTeacher = createTeacherForFinanceModal();
    $training = createTrainingForFinanceModal($ownerTeacher);

    Livewire::actingAs($otherTeacher)
        ->test(EditFinanceModal::class, ['trainingId' => $training->id])
        ->assertForbidden();
});

it('refreshes training values in the details view when finance update event is received', function (): void {
    $teacher = createTeacherForFinanceModal();
    $training = createTrainingForFinanceModal($teacher);
    $paidStudent = User::factory()->create();
    $training->students()->attach($paidStudent->id, ['kit' => 0, 'accredited' => 0, 'payment' => 1]);

    $component = Livewire::test(TrainingView::class, ['training' => $training])
        ->assertSee('100,00')
        ->assertSee('0,00');

    $training->update([
        'price_church' => '12,00',
        'discount' => '2,00',
    ]);

    $component
        ->dispatch('training-finance-updated', trainingId: $training->id)
        ->assertSee('12,00')
        ->assertSee('2,00');
});
