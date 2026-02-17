<?php

use App\Livewire\Pages\App\Teacher\Training\ManageMentorsModal;
use App\Models\Church;
use App\Models\Course;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use App\Services\Training\MentorAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createTeacherForMentorsModal(): User
{
    $teacher = User::factory()->create(['church_id' => null]);
    $teacherRole = Role::query()->firstOrCreate(['name' => 'Teacher']);
    $teacher->roles()->syncWithoutDetaching([$teacherRole->id]);

    return $teacher;
}

function createTrainingForMentorsModal(User $teacher): Training
{
    $course = Course::factory()->create();
    $hostChurch = Church::factory()->create();

    return Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
        'church_id' => $hostChurch->id,
    ]);
}

it('lists mentors for the selected training in the modal', function (): void {
    $teacher = createTeacherForMentorsModal();
    $training = createTrainingForMentorsModal($teacher);
    $mentorChurch = Church::factory()->create(['name' => 'Igreja Mentor Alfa']);
    $mentor = User::factory()->create([
        'name' => 'Mentor Alfa',
        'email' => 'mentor.alfa@example.test',
        'church_id' => $mentorChurch->id,
    ]);

    app(MentorAssignmentService::class)->addMentor($training, $mentor, $teacher);

    Livewire::actingAs($teacher)
        ->test(ManageMentorsModal::class, ['trainingId' => $training->id])
        ->call('openModal', $training->id)
        ->assertSee('Mentor Alfa')
        ->assertSee('mentor.alfa@example.test')
        ->assertSee('Igreja Mentor Alfa');
});

it('adds mentor by selecting a searched user', function (): void {
    $teacher = createTeacherForMentorsModal();
    $training = createTrainingForMentorsModal($teacher);
    $candidate = User::factory()->create([
        'name' => 'Mentor Beta',
        'email' => 'mentor.beta@example.test',
    ]);

    Livewire::actingAs($teacher)
        ->test(ManageMentorsModal::class, ['trainingId' => $training->id])
        ->call('openModal', $training->id)
        ->set('userSearch', 'mentor.beta')
        ->call('addMentor', $candidate->id)
        ->assertSee('Mentor Beta');

    $this->assertDatabaseHas('mentors', [
        'training_id' => $training->id,
        'user_id' => $candidate->id,
        'created_by' => $teacher->id,
    ]);
});

it('removes mentor from training', function (): void {
    $teacher = createTeacherForMentorsModal();
    $training = createTrainingForMentorsModal($teacher);
    $mentor = User::factory()->create();

    app(MentorAssignmentService::class)->addMentor($training, $mentor, $teacher);

    Livewire::actingAs($teacher)
        ->test(ManageMentorsModal::class, ['trainingId' => $training->id])
        ->call('openModal', $training->id)
        ->call('removeMentor', $mentor->id);

    $this->assertDatabaseMissing('mentors', [
        'training_id' => $training->id,
        'user_id' => $mentor->id,
    ]);
});

it('blocks non-teachers from managing mentors modal', function (): void {
    $teacher = createTeacherForMentorsModal();
    $training = createTrainingForMentorsModal($teacher);
    $nonTeacher = User::factory()->create();

    Livewire::actingAs($nonTeacher)
        ->test(ManageMentorsModal::class, ['trainingId' => $training->id])
        ->assertForbidden();
});
