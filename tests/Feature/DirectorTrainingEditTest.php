<?php

use App\Livewire\Pages\App\Director\Training\Edit;
use App\Models\Course;
use App\Models\Training;
use App\Models\User;
use App\TrainingStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('updates training data and event dates', function () {
    $course = Course::factory()->create(['execution' => 0]);
    $newCourse = Course::factory()->create(['execution' => 0]);
    $teacher = User::factory()->create();
    $newCourse->teachers()->attach($teacher->id, ['status' => 1]);

    $training = Training::factory()->create([
        'course_id' => $course->id,
        'status' => TrainingStatus::Planning->value,
    ]);

    $training->eventDates()->create([
        'date' => '2026-01-10',
        'start_time' => '08:00:00',
        'end_time' => '12:00:00',
    ]);

    Livewire::test(Edit::class, ['training' => $training])
        ->set('course_id', $newCourse->id)
        ->set('teacher_id', $teacher->id)
        ->set('coordinator', 'Novo Coordenador')
        ->set('status', TrainingStatus::Scheduled->value)
        ->set('eventDates', [
            ['date' => '2026-02-11', 'start_time' => '09:00', 'end_time' => '13:00'],
        ])
        ->call('submit');

    $training->refresh();

    expect($training->course_id)->toBe($newCourse->id)
        ->and($training->teacher_id)->toBe($teacher->id)
        ->and($training->coordinator)->toBe('Novo Coordenador')
        ->and($training->status->value)->toBe(TrainingStatus::Scheduled->value);

    $eventDates = $training->eventDates()->get();
    expect($eventDates)->toHaveCount(1)
        ->and($eventDates->first()->date)->toBe('2026-02-11')
        ->and($eventDates->first()->start_time)->toBe('09:00:00')
        ->and($eventDates->first()->end_time)->toBe('13:00:00');
});

it('includes the current church in the edit dropdown', function () {
    $church = \App\Models\Church::factory()->create();
    $course = Course::factory()->create(['execution' => 0]);
    $teacher = User::factory()->create();
    $training = Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
    ]);

    Livewire::test(Edit::class, ['training' => $training])
        ->assertViewHas('churches', function ($churches) use ($church): bool {
            return $churches->pluck('id')->contains($church->id);
        });
});
