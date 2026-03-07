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

function createTrainingManager(string $roleName): User
{
    $user = User::factory()->create();
    $role = Role::query()->firstOrCreate(['name' => $roleName]);
    $user->roles()->syncWithoutDetaching([$role->id]);

    return $user;
}

function makeScheduledTrainingOverdue(Training $training): Training
{
    $training->update([
        'status' => TrainingStatus::Scheduled,
    ]);

    $training->eventDates()
        ->orderBy('date')
        ->orderBy('start_time')
        ->get()
        ->each(function ($eventDate, int $index): void {
            $eventDate->update([
                'date' => Carbon::yesterday()->subDays($index)->format('Y-m-d'),
            ]);
        });

    return $training->fresh(['course', 'eventDates']);
}

function makeScheduledTrainingUpcoming(Training $training): Training
{
    $training->update([
        'status' => TrainingStatus::Scheduled,
    ]);

    $training->eventDates()
        ->orderBy('date')
        ->orderBy('start_time')
        ->get()
        ->each(function ($eventDate, int $index): void {
            $eventDate->update([
                'date' => Carbon::tomorrow()->addDays($index)->format('Y-m-d'),
            ]);
        });

    return $training->fresh(['course', 'eventDates']);
}

function createTrainingForCompletionReviewAlert(): Training
{
    $church = Church::factory()->create();
    $course = Course::factory()->create([
        'execution' => 0,
    ]);

    return Training::factory()->create([
        'church_id' => $church->id,
        'course_id' => $course->id,
    ]);
}

dataset('training manager routes', [
    'teacher' => [
        'Teacher',
        fn (): string => route('app.teacher.trainings.scheduled'),
        fn (Training $training): string => route('app.teacher.trainings.show', $training),
    ],
    'director' => [
        'Director',
        fn (): string => route('app.director.training.scheduled'),
        fn (Training $training): string => route('app.director.training.show', $training),
    ],
]);

it('shows the completion review alert in scheduled lists for overdue trainings', function (string $role, callable $listRoute, callable $showRoute): void {
    $user = createTrainingManager($role);
    $training = makeScheduledTrainingOverdue(createTrainingForCompletionReviewAlert());

    $response = $this
        ->actingAs($user)
        ->get($listRoute());

    $response->assertOk();
    $response->assertSee('Alerta de evento');
    $response->assertSee('Revise e complete todas as informações do treinamento, depois marque este evento como concluído.');
})->with('training manager routes');

it('shows the completion review alert on overdue training details', function (string $role, callable $listRoute, callable $showRoute): void {
    $user = createTrainingManager($role);
    $training = makeScheduledTrainingOverdue(createTrainingForCompletionReviewAlert());

    $response = $this
        ->actingAs($user)
        ->get($showRoute($training));

    $response->assertOk();
    $response->assertSee('Evento agendado com data passada');
    $response->assertSee('Revise e complete todas as informações do treinamento, depois marque este evento como concluído.');
})->with('training manager routes');

it('does not show the completion review alert on upcoming scheduled training details', function (string $role, callable $listRoute, callable $showRoute): void {
    $user = createTrainingManager($role);
    $training = makeScheduledTrainingUpcoming(createTrainingForCompletionReviewAlert());

    $response = $this
        ->actingAs($user)
        ->get($showRoute($training));

    $response->assertOk();
    $response->assertDontSee('Evento agendado com data passada');
    $response->assertDontSee('Revise e complete todas as informações do treinamento, depois marque este evento como concluído.');
})->with('training manager routes');
