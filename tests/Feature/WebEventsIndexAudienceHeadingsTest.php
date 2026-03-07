<?php

use App\Models\Course;
use App\Models\Ministry;
use App\Models\Training;
use App\TrainingStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('does not show members carousel header when there are no members events', function () {
    $ministry = Ministry::query()->create([
        'initials' => 'MIN1',
        'name' => 'Ministerio 1',
    ]);

    $courseLeaders = Course::factory()->create([
        'execution' => 0,
        'name' => 'Curso Lideres',
        'ministry_id' => $ministry->id,
    ]);

    $trainingLeaders = Training::factory()->create([
        'course_id' => $courseLeaders->id,
        'status' => TrainingStatus::Scheduled,
    ]);

    $trainingLeaders->eventDates()->delete();
    $trainingLeaders->eventDates()->create([
        'date' => now()->addDays(5)->toDateString(),
        'start_time' => '09:00:00',
        'end_time' => '11:00:00',
    ]);

    $response = $this->get(route('web.event.index'));

    $response->assertOk();
    $response->assertSeeText('Treinamentos para Líderes');
    $response->assertDontSeeText('Treinamentos para Membros');
});

it('does not show members carousel header even when there are only member events', function () {
    $ministry = Ministry::query()->create([
        'initials' => 'MIN1',
        'name' => 'Ministerio 1',
    ]);

    Course::factory()->create([
        'execution' => 0,
        'name' => 'Curso Lideres',
        'ministry_id' => $ministry->id,
    ]);
    $courseMembers = Course::factory()->create([
        'execution' => 1,
        'name' => 'Curso Membros Especial',
        'ministry_id' => $ministry->id,
    ]);

    $trainingMembers = Training::factory()->create([
        'course_id' => $courseMembers->id,
        'status' => TrainingStatus::Scheduled,
    ]);

    $trainingMembers->eventDates()->delete();
    $trainingMembers->eventDates()->create([
        'date' => now()->addDays(6)->toDateString(),
        'start_time' => '10:00:00',
        'end_time' => '12:00:00',
    ]);

    $response = $this->get(route('web.event.index'));

    $response->assertOk();
    $response->assertDontSeeText('Treinamentos para Membros');
    $response->assertDontSeeText('Curso Membros Especial');
});

it('shows leadership training types grouped inside the ministry section', function () {
    $ministry = Ministry::query()->create([
        'id' => 1,
        'initials' => 'MIN1',
        'name' => 'Ministerio 1',
    ]);

    $clinicCourse = Course::factory()->create([
        'execution' => 0,
        'type' => 'Clínica',
        'name' => 'Curso Lideres A',
        'targetAudience' => 'Líderes e facilitadores locais',
        'ministry_id' => $ministry->id,
    ]);
    $workshopCourse = Course::factory()->create([
        'execution' => 0,
        'type' => 'Workshop',
        'name' => 'Curso Lideres B',
        'targetAudience' => 'Professores e coordenadores de ministério',
        'ministry_id' => $ministry->id,
    ]);

    $clinicTraining = Training::factory()->create([
        'course_id' => $clinicCourse->id,
        'status' => TrainingStatus::Scheduled,
    ]);
    $workshopTraining = Training::factory()->create([
        'course_id' => $workshopCourse->id,
        'status' => TrainingStatus::Scheduled,
    ]);

    $clinicTraining->eventDates()->delete();
    $workshopTraining->eventDates()->delete();

    $clinicTraining->eventDates()->create([
        'date' => now()->addDays(5)->toDateString(),
        'start_time' => '09:00:00',
        'end_time' => '11:00:00',
    ]);
    $workshopTraining->eventDates()->create([
        'date' => now()->addDays(6)->toDateString(),
        'start_time' => '10:00:00',
        'end_time' => '12:00:00',
    ]);

    $response = $this->get(route('web.event.index'));

    $response->assertOk();
    $response->assertSeeText('Clínica');
    $response->assertSeeText('Workshop');
    $response->assertSeeText('Líderes e facilitadores locais');
    $response->assertSeeText('Professores e coordenadores de ministério');
});
