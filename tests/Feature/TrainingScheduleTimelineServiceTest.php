<?php

use App\Models\Course;
use App\Models\EventDate;
use App\Models\Training;
use App\Services\Schedule\TrainingScheduleTimelineService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('reflows items with a one second gap between sessions', function () {
    $course = Course::factory()->create();
    $training = Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => null,
        'church_id' => null,
    ]);
    $training->eventDates()->delete();

    EventDate::query()->create([
        'training_id' => $training->id,
        'date' => '2026-02-10',
        'start_time' => '09:00:00',
        'end_time' => '12:00:00',
    ]);

    $first = $training->scheduleItems()->create([
        'section_id' => null,
        'date' => '2026-02-10',
        'starts_at' => Carbon::parse('2026-02-10 08:00:00'),
        'ends_at' => Carbon::parse('2026-02-10 09:00:00'),
        'type' => 'SECTION',
        'title' => 'Primeira',
        'planned_duration_minutes' => 60,
        'suggested_duration_minutes' => null,
        'min_duration_minutes' => null,
        'origin' => 'TEACHER',
        'status' => 'OK',
        'conflict_reason' => null,
        'meta' => null,
        'position' => 1,
    ]);

    $second = $training->scheduleItems()->create([
        'section_id' => null,
        'date' => '2026-02-10',
        'starts_at' => Carbon::parse('2026-02-10 09:00:00'),
        'ends_at' => Carbon::parse('2026-02-10 09:30:00'),
        'type' => 'SECTION',
        'title' => 'Segunda',
        'planned_duration_minutes' => 30,
        'suggested_duration_minutes' => null,
        'min_duration_minutes' => null,
        'origin' => 'TEACHER',
        'status' => 'OK',
        'conflict_reason' => null,
        'meta' => null,
        'position' => 2,
    ]);

    app(TrainingScheduleTimelineService::class)->reflowDay($training->id, '2026-02-10');

    $first->refresh();
    $second->refresh();

    expect($first->starts_at->format('H:i:s'))->toBe('09:00:00');
    expect($first->ends_at->format('H:i:s'))->toBe('10:00:00');
    expect($second->starts_at->format('H:i:s'))->toBe('10:00:01');
    expect($second->ends_at->format('H:i:s'))->toBe('10:30:01');
});

it('moves items across days and keeps positions sequential', function () {
    $course = Course::factory()->create();
    $training = Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => null,
        'church_id' => null,
    ]);
    $training->eventDates()->delete();

    EventDate::query()->create([
        'training_id' => $training->id,
        'date' => '2026-02-10',
        'start_time' => '08:00:00',
        'end_time' => '12:00:00',
    ]);

    EventDate::query()->create([
        'training_id' => $training->id,
        'date' => '2026-02-11',
        'start_time' => '09:00:00',
        'end_time' => '12:00:00',
    ]);

    $first = $training->scheduleItems()->create([
        'section_id' => null,
        'date' => '2026-02-10',
        'starts_at' => Carbon::parse('2026-02-10 08:00:00'),
        'ends_at' => Carbon::parse('2026-02-10 09:00:00'),
        'type' => 'SECTION',
        'title' => 'Primeira',
        'planned_duration_minutes' => 60,
        'suggested_duration_minutes' => null,
        'min_duration_minutes' => null,
        'origin' => 'TEACHER',
        'status' => 'OK',
        'conflict_reason' => null,
        'meta' => null,
        'position' => 1,
    ]);

    $second = $training->scheduleItems()->create([
        'section_id' => null,
        'date' => '2026-02-10',
        'starts_at' => Carbon::parse('2026-02-10 09:00:00'),
        'ends_at' => Carbon::parse('2026-02-10 09:30:00'),
        'type' => 'SECTION',
        'title' => 'Segunda',
        'planned_duration_minutes' => 30,
        'suggested_duration_minutes' => null,
        'min_duration_minutes' => null,
        'origin' => 'TEACHER',
        'status' => 'OK',
        'conflict_reason' => null,
        'meta' => null,
        'position' => 2,
    ]);

    $third = $training->scheduleItems()->create([
        'section_id' => null,
        'date' => '2026-02-11',
        'starts_at' => Carbon::parse('2026-02-11 09:00:00'),
        'ends_at' => Carbon::parse('2026-02-11 10:00:00'),
        'type' => 'SECTION',
        'title' => 'Terceira',
        'planned_duration_minutes' => 60,
        'suggested_duration_minutes' => null,
        'min_duration_minutes' => null,
        'origin' => 'TEACHER',
        'status' => 'OK',
        'conflict_reason' => null,
        'meta' => null,
        'position' => 1,
    ]);

    app(TrainingScheduleTimelineService::class)->moveAfter(
        $training->id,
        $second->id,
        '2026-02-11',
        $third->id,
    );

    $second->refresh();
    $third->refresh();
    $first->refresh();

    expect($second->date->format('Y-m-d'))->toBe('2026-02-11');
    expect($third->position)->toBe(1);
    expect($second->position)->toBe(2);
    expect($second->starts_at->format('H:i:s'))->toBe('10:00:01');
    expect($second->ends_at->format('H:i:s'))->toBe('10:30:01');

    $remaining = $training->scheduleItems()
        ->whereDate('date', '2026-02-10')
        ->orderBy('position')
        ->get();

    expect($remaining)->toHaveCount(1);
    expect($remaining->first()?->id)->toBe($first->id);
    expect($remaining->first()?->position)->toBe(1);
});
