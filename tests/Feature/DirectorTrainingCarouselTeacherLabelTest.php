<?php

use App\Models\Course;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use App\TrainingStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createUserWithTrainingRole(string $roleName): User
{
    $user = User::factory()->create();
    $role = Role::query()->firstOrCreate(['name' => $roleName]);
    $user->roles()->syncWithoutDetaching([$role->id]);

    return $user;
}

it('shows the principal teacher name in the director training carousel footer', function (): void {
    $director = createUserWithTrainingRole('Director');
    $principalTeacher = User::factory()->create(['name' => 'Professora Raquel']);
    $course = Course::factory()->create([
        'execution' => 0,
        'name' => 'Treinamento Director',
    ]);

    $training = Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $principalTeacher->id,
        'status' => TrainingStatus::Scheduled,
    ]);
    $training->eventDates()->delete();
    $training->eventDates()->create([
        'date' => now()->addWeek()->toDateString(),
        'start_time' => '08:00:00',
        'end_time' => '17:00:00',
    ]);

    $response = $this->actingAs($director)
        ->get(route('app.director.training.scheduled'));

    $response
        ->assertOk()
        ->assertSee('Treinamento Director')
        ->assertSee('Professora Raquel')
        ->assertDontSee('Saiba mais.');
});

it('keeps the saiba mais footer in the teacher training carousel', function (): void {
    $teacher = createUserWithTrainingRole('Teacher');
    $course = Course::factory()->create([
        'execution' => 0,
        'name' => 'Treinamento Teacher',
    ]);

    $training = Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
        'status' => TrainingStatus::Scheduled,
    ]);
    $training->eventDates()->delete();
    $training->eventDates()->create([
        'date' => now()->addWeek()->toDateString(),
        'start_time' => '08:00:00',
        'end_time' => '17:00:00',
    ]);

    $response = $this->actingAs($teacher)
        ->get(route('app.teacher.trainings.scheduled'));

    $response
        ->assertOk()
        ->assertSee('Treinamento Teacher')
        ->assertSee('Saiba mais.');
});
