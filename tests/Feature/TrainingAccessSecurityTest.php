<?php

use App\Livewire\Pages\App\Teacher\Training\Index as TeacherTrainingIndex;
use App\Models\Church;
use App\Models\Course;
use App\Models\Role;
use App\Models\StpApproach;
use App\Models\StpSession;
use App\Models\Training;
use App\Models\User;
use App\TrainingStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createUserForTrainingAccess(string $roleName): User
{
    $user = User::factory()->create();
    $role = Role::query()->firstOrCreate(['name' => $roleName]);
    $user->roles()->syncWithoutDetaching([$role->id]);

    return $user;
}

it('allows directors to access another teachers training by url', function (): void {
    $director = createUserForTrainingAccess('Director');
    $teacher = createUserForTrainingAccess('Teacher');
    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
    ]);

    $this->actingAs($director)
        ->get(route('app.director.training.show', $training))
        ->assertOk();

    $this->actingAs($director)
        ->get(route('app.director.training.schedule', $training))
        ->assertOk();
});

it('allows directors to see the national dashboard with data from any teacher', function (): void {
    $director = createUserForTrainingAccess('Director');
    $teacherOne = createUserForTrainingAccess('Teacher');
    $teacherTwo = createUserForTrainingAccess('Teacher');
    $trainingOne = Training::factory()->create(['teacher_id' => $teacherOne->id]);
    $trainingTwo = Training::factory()->create(['teacher_id' => $teacherTwo->id]);

    $this->actingAs($director)
        ->get(route('app.director.dashboard'))
        ->assertOk()
        ->assertSee((string) $trainingOne->course?->name)
        ->assertSee((string) $trainingTwo->course?->name);
});

it('denies teacher-directors access to other teachers trainings through teacher urls', function (): void {
    $teacherDirector = createUserForTrainingAccess('Teacher');
    $teacherDirector->roles()->syncWithoutDetaching([
        Role::query()->firstOrCreate(['name' => 'Director'])->id,
    ]);
    $ownerTeacher = createUserForTrainingAccess('Teacher');
    $training = Training::factory()->create([
        'teacher_id' => $ownerTeacher->id,
    ]);

    $this->actingAs($teacherDirector)
        ->get(route('app.teacher.trainings.show', $training))
        ->assertForbidden();

    $this->actingAs($teacherDirector)
        ->get(route('app.teacher.trainings.schedule', $training))
        ->assertForbidden();

    $this->actingAs($teacherDirector)
        ->get(route('app.teacher.trainings.registrations', $training))
        ->assertForbidden();

    $this->actingAs($teacherDirector)
        ->get(route('app.teacher.trainings.statistics', $training))
        ->assertForbidden();
});

it('keeps mentors in read only mode for training and stp approach', function (): void {
    $mentor = createUserForTrainingAccess('Mentor');
    $creator = User::factory()->create();
    $training = Training::factory()->create();
    $training->mentors()->attach($mentor->id, ['created_by' => $creator->id]);
    $session = StpSession::query()->create([
        'training_id' => $training->id,
        'sequence' => 1,
        'label' => 'STP 1',
        'starts_at' => now(),
        'ends_at' => now()->addHour(),
        'status' => 'planned',
    ]);
    $approach = StpApproach::query()->create([
        'training_id' => $training->id,
        'stp_session_id' => $session->id,
        'stp_team_id' => null,
        'type' => 'visitor',
        'status' => 'planned',
        'position' => 1,
        'person_name' => 'Contato',
        'created_by_user_id' => $creator->id,
    ]);

    expect(Gate::forUser($mentor)->allows('view', $training))->toBeTrue()
        ->and(Gate::forUser($mentor)->allows('update', $training))->toBeFalse()
        ->and(Gate::forUser($mentor)->allows('delete', $training))->toBeFalse()
        ->and(Gate::forUser($mentor)->allows('view', $approach))->toBeTrue()
        ->and(Gate::forUser($mentor)->allows('update', $approach))->toBeFalse()
        ->and(Gate::forUser($mentor)->allows('delete', $approach))->toBeFalse();
});

it('scopes the teacher livewire training index to owned and assisted trainings', function (): void {
    $teacher = createUserForTrainingAccess('Teacher');
    $otherTeacher = createUserForTrainingAccess('Teacher');
    $course = Course::factory()->create([
        'execution' => 0,
        'name' => 'Curso Escopo',
    ]);
    $ownedChurch = Church::factory()->create(['name' => 'Igreja Visivel Titular']);
    $assistedChurch = Church::factory()->create(['name' => 'Igreja Visivel Auxiliar']);
    $hiddenChurch = Church::factory()->create(['name' => 'Igreja Oculta']);

    $ownedTraining = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'course_id' => $course->id,
        'church_id' => $ownedChurch->id,
        'status' => TrainingStatus::Scheduled,
    ]);
    $assistedTraining = Training::factory()->create([
        'teacher_id' => $otherTeacher->id,
        'course_id' => $course->id,
        'church_id' => $assistedChurch->id,
        'status' => TrainingStatus::Scheduled,
    ]);
    $assistedTraining->assistantTeachers()->attach($teacher->id);
    $hiddenTraining = Training::factory()->create([
        'teacher_id' => $otherTeacher->id,
        'course_id' => $course->id,
        'church_id' => $hiddenChurch->id,
        'status' => TrainingStatus::Scheduled,
    ]);

    Livewire::actingAs($teacher)
        ->test(TeacherTrainingIndex::class, ['statusKey' => 'scheduled'])
        ->assertSee(route('app.teacher.trainings.show', $ownedTraining), false)
        ->assertSee(route('app.teacher.trainings.show', $assistedTraining), false)
        ->assertDontSee(route('app.teacher.trainings.show', $hiddenTraining), false);
});
