<?php

use App\Livewire\Pages\App\Teacher\Training\EventTeachers;
use App\Models\Church;
use App\Models\Course;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createTeacherWithRoleForEventTeachers(): User
{
    $teacher = User::factory()->create();
    $teacherRole = Role::query()->firstOrCreate(['name' => 'Teacher']);
    $teacher->roles()->syncWithoutDetaching([$teacherRole->id]);

    return $teacher;
}

function createTrainingForEventTeachers(Course $course, User $teacher): Training
{
    return Training::query()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
        'church_id' => Church::factory()->create()->id,
        'status' => 0,
    ]);
}

it('updates principal teacher and assistant teachers from the modal component', function (): void {
    $ownerTeacher = createTeacherWithRoleForEventTeachers();
    $newPrincipalTeacher = createTeacherWithRoleForEventTeachers();
    $assistantTeacher = User::factory()->create();

    $course = Course::factory()->create();
    $course->teachers()->syncWithoutDetaching([
        $ownerTeacher->id => ['status' => 1],
        $newPrincipalTeacher->id => ['status' => 1],
    ]);

    $training = createTrainingForEventTeachers($course, $ownerTeacher);

    $component = Livewire::actingAs($ownerTeacher)
        ->test(EventTeachers::class, ['trainingId' => $training->id])
        ->set('teacherId', $newPrincipalTeacher->id)
        ->call('requestPrincipalTeacherChange', (string) $newPrincipalTeacher->id)
        ->assertSet('showPrincipalChangeConfirmation', true)
        ->call('confirmPrincipalTeacherChange')
        ->assertSet('showPrincipalChangeConfirmation', false)
        ->set('assistantSearch', $assistantTeacher->name)
        ->call('addAssistantTeacher', $assistantTeacher->id)
        ->call('requestSave');

    $component
        ->assertRedirect(route('app.teacher.trainings.index'))
        ->assertHasNoErrors();

    $training->refresh();

    expect($training->teacher_id)->toBe($newPrincipalTeacher->id)
        ->and($training->assistantTeachers()->pluck('users.id')->all())
        ->toBe([$assistantTeacher->id]);
});

it('opens confirmation modal on principal teacher select change', function (): void {
    $ownerTeacher = createTeacherWithRoleForEventTeachers();
    $newPrincipalTeacher = createTeacherWithRoleForEventTeachers();

    $course = Course::factory()->create();
    $course->teachers()->syncWithoutDetaching([
        $ownerTeacher->id => ['status' => 1],
        $newPrincipalTeacher->id => ['status' => 1],
    ]);

    $training = createTrainingForEventTeachers($course, $ownerTeacher);

    Livewire::actingAs($ownerTeacher)
        ->test(EventTeachers::class, ['trainingId' => $training->id])
        ->set('teacherId', $newPrincipalTeacher->id)
        ->call('requestPrincipalTeacherChange', (string) $newPrincipalTeacher->id)
        ->assertSet('showPrincipalChangeConfirmation', true);
});

it('links event churches to accredited assistant teachers', function (): void {
    $ownerTeacher = createTeacherWithRoleForEventTeachers();
    $accreditedAssistantTeacher = createTeacherWithRoleForEventTeachers();
    $hostChurch = Church::factory()->create();
    $participantChurchA = Church::factory()->create();
    $participantChurchB = Church::factory()->create();

    $course = Course::factory()->create();
    $course->teachers()->syncWithoutDetaching([
        $ownerTeacher->id => ['status' => 1],
    ]);

    $training = Training::query()->create([
        'course_id' => $course->id,
        'teacher_id' => $ownerTeacher->id,
        'church_id' => $hostChurch->id,
        'status' => 0,
    ]);

    $participantOne = User::factory()->create(['church_id' => $participantChurchA->id]);
    $participantTwo = User::factory()->create(['church_id' => $participantChurchB->id]);
    $training->students()->attach($participantOne->id, ['accredited' => 0, 'kit' => 0, 'payment' => 0]);
    $training->students()->attach($participantTwo->id, ['accredited' => 0, 'kit' => 0, 'payment' => 0]);

    Livewire::actingAs($ownerTeacher)
        ->test(EventTeachers::class, ['trainingId' => $training->id])
        ->set('assistantSearch', $accreditedAssistantTeacher->name)
        ->call('addAssistantTeacher', $accreditedAssistantTeacher->id)
        ->call('requestSave')
        ->assertHasNoErrors();

    $linkedChurchIds = Church::query()
        ->join('church_missionary', 'church_missionary.church_id', '=', 'churches.id')
        ->where('church_missionary.user_id', $accreditedAssistantTeacher->id)
        ->pluck('churches.id')
        ->sort()
        ->values()
        ->all();

    $expectedChurchIds = collect([$hostChurch->id, $participantChurchA->id, $participantChurchB->id])
        ->sort()
        ->values()
        ->all();

    expect($linkedChurchIds)->toBe($expectedChurchIds);
});

it('requires the principal teacher to be a certified teacher from the course', function (): void {
    $ownerTeacher = createTeacherWithRoleForEventTeachers();
    $invalidPrincipal = User::factory()->create();

    $course = Course::factory()->create();
    $course->teachers()->syncWithoutDetaching([
        $ownerTeacher->id => ['status' => 1],
        $invalidPrincipal->id => ['status' => 1],
    ]);

    $training = createTrainingForEventTeachers($course, $ownerTeacher);

    Livewire::actingAs($ownerTeacher)
        ->test(EventTeachers::class, ['trainingId' => $training->id])
        ->set('teacherId', $invalidPrincipal->id)
        ->call('requestSave')
        ->assertHasErrors(['teacherId']);

    expect($training->fresh()->teacher_id)->toBe($ownerTeacher->id);
});

it('shows the manage teachers action in the training details toolbar', function (): void {
    $teacher = createTeacherWithRoleForEventTeachers();
    $course = Course::factory()->create();
    $course->teachers()->syncWithoutDetaching([
        $teacher->id => ['status' => 1],
    ]);

    $training = createTrainingForEventTeachers($course, $teacher);

    $this->actingAs($teacher)
        ->get(route('app.teacher.trainings.show', $training))
        ->assertOk()
        ->assertSee('Professores');
});
