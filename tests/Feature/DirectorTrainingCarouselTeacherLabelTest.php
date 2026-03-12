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
    $studentA = User::factory()->create();
    $studentB = User::factory()->create();
    $course = Course::factory()->create([
        'execution' => 0,
        'name' => 'Treinamento Director',
    ]);

    $training = Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $principalTeacher->id,
        'status' => TrainingStatus::Scheduled,
        'price' => 150,
    ]);
    $training->eventDates()->delete();
    $training->eventDates()->create([
        'date' => now()->addWeek()->toDateString(),
        'start_time' => '08:00:00',
        'end_time' => '17:00:00',
    ]);
    $training->students()->attach([$studentA->id, $studentB->id]);

    $response = $this->actingAs($director)
        ->get(route('app.director.training.scheduled'));

    $response
        ->assertOk()
        ->assertSee('Treinamento Director')
        ->assertSee('Professora Raquel')
        ->assertSee('2 alunos inscritos')
        ->assertSee('Evento pago')
        ->assertDontSee('Evento gratuito')
        ->assertDontSee('Saiba mais.');
});

it('keeps the saiba mais footer in the teacher training carousel', function (): void {
    $teacher = createUserWithTrainingRole('Teacher');
    $studentA = User::factory()->create();
    $studentB = User::factory()->create();
    $studentC = User::factory()->create();
    $course = Course::factory()->create([
        'execution' => 0,
        'name' => 'Treinamento Teacher',
    ]);

    $training = Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
        'status' => TrainingStatus::Scheduled,
        'price' => 0,
        'price_church' => 0,
        'discount' => 0,
    ]);
    $training->eventDates()->delete();
    $training->eventDates()->create([
        'date' => now()->addWeek()->toDateString(),
        'start_time' => '08:00:00',
        'end_time' => '17:00:00',
    ]);
    $training->students()->attach([$studentA->id, $studentB->id, $studentC->id]);

    $response = $this->actingAs($teacher)
        ->get(route('app.teacher.trainings.scheduled'));

    $response
        ->assertOk()
        ->assertSee('Treinamento Teacher')
        ->assertSee('3 alunos inscritos')
        ->assertSee('Evento gratuito')
        ->assertDontSee('Evento pago')
        ->assertSee('Saiba mais.');
});
