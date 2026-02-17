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

it('shows members carousel header when there are members events', function () {
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
        'name' => 'Curso Membros',
        'ministry_id' => $ministry->id,
    ]);

    expect($courseMembers->id)->toBe(2);

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
    $response->assertSeeText('Treinamentos para Membros');
});
