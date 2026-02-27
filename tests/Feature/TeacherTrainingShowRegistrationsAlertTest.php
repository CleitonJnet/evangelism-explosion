<?php

use App\Models\Church;
use App\Models\ChurchTemp;
use App\Models\Role;
use App\Models\Training;
use App\Models\TrainingScheduleItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createTeacherForShowRegistrationsAlert(): User
{
    $teacher = User::factory()->create(['church_id' => null]);
    $teacherRole = Role::query()->firstOrCreate(['name' => 'Teacher']);
    $teacher->roles()->syncWithoutDetaching([$teacherRole->id]);

    return $teacher;
}

function makeTrainingScheduleMatchEventDates(Training $training): void
{
    $training->load('eventDates');

    foreach ($training->eventDates as $eventDate) {
        $date = Carbon::parse((string) $eventDate->date)->format('Y-m-d');
        $startsAt = Carbon::parse($date.' '.$eventDate->start_time);
        $endsAt = Carbon::parse($date.' '.$eventDate->end_time);

        TrainingScheduleItem::factory()->create([
            'training_id' => $training->id,
            'date' => $date,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ]);
    }
}

it('shows registrations alert on toolbar when there are pending church issues', function () {
    $teacher = createTeacherForShowRegistrationsAlert();
    $hostChurch = Church::factory()->create();
    $officialChurch = Church::factory()->create();
    $pendingChurchTemp = ChurchTemp::query()->create([
        'name' => 'Igreja em Analise',
        'city' => 'Recife',
        'state' => 'PE',
        'status' => 'pending',
        'normalized_name' => 'igreja em analise',
    ]);

    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $hostChurch->id,
    ]);
    makeTrainingScheduleMatchEventDates($training);

    $validStudent = User::factory()->create(['church_id' => $officialChurch->id, 'church_temp_id' => null]);
    $noChurchStudent = User::factory()->create(['church_id' => null, 'church_temp_id' => null]);
    $pendingChurchStudent = User::factory()->create(['church_id' => null, 'church_temp_id' => $pendingChurchTemp->id]);

    $training->students()->attach($validStudent->id);
    $training->students()->attach($noChurchStudent->id);
    $training->students()->attach($pendingChurchStudent->id);

    $response = $this
        ->actingAs($teacher)
        ->get(route('app.teacher.trainings.show', $training));

    $response->assertOk();
    $response->assertSee('images/alarme.webp', false);
});

it('does not show registrations alert on toolbar when all students have validated churches', function () {
    $teacher = createTeacherForShowRegistrationsAlert();
    $hostChurch = Church::factory()->create();
    $officialChurch = Church::factory()->create();

    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $hostChurch->id,
    ]);
    makeTrainingScheduleMatchEventDates($training);

    $validStudentOne = User::factory()->create(['church_id' => $officialChurch->id, 'church_temp_id' => null]);
    $validStudentTwo = User::factory()->create(['church_id' => $officialChurch->id, 'church_temp_id' => null]);

    $training->students()->attach($validStudentOne->id);
    $training->students()->attach($validStudentTwo->id);

    $response = $this
        ->actingAs($teacher)
        ->get(route('app.teacher.trainings.show', $training));

    $response->assertOk();
    $response->assertDontSee('images/alarme.webp', false);
});
