<?php

use App\Models\Church;
use App\Models\Course;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use App\TrainingStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

function createCompletedTrainingsListUser(string $roleName): User
{
    $user = User::factory()->create();
    $role = Role::query()->firstOrCreate(['name' => $roleName]);
    $user->roles()->syncWithoutDetaching([$role->id]);

    return $user;
}

function createCompletedTrainingForList(Course $course, string $city, string $date): Training
{
    $training = Training::factory()->create([
        'course_id' => $course->id,
        'church_id' => Church::factory()->create()->id,
        'city' => $city,
        'state' => 'SP',
        'status' => TrainingStatus::Completed,
    ]);

    $training->eventDates()
        ->orderBy('date')
        ->orderBy('start_time')
        ->get()
        ->each(function ($eventDate, int $index) use ($date): void {
            $eventDate->update([
                'date' => Carbon::parse($date)->addDays($index)->format('Y-m-d'),
            ]);
        });

    return $training->fresh(['eventDates']);
}

dataset('completed trainings list routes', [
    'teacher' => [
        'Teacher',
        fn (): string => route('app.teacher.trainings.completed'),
    ],
    'director' => [
        'Director',
        fn (): string => route('app.director.training.completed'),
    ],
]);

it('shows completed trainings in descending order and with full date on the list', function (string $role, callable $routeResolver): void {
    $user = createCompletedTrainingsListUser($role);
    $course = Course::factory()->create([
        'execution' => 0,
        'name' => 'Treinamento de Perfil',
    ]);

    createCompletedTrainingForList($course, 'Cidade Antiga', '2026-03-01');
    createCompletedTrainingForList($course, 'Cidade Recente', '2026-03-05');

    $response = $this
        ->actingAs($user)
        ->get($routeResolver());

    $response->assertOk();
    $response->assertSeeInOrder(['Cidade Recente, SP', 'Cidade Antiga, SP']);
    $response->assertSee('05/03/2026');
})->with('completed trainings list routes');
