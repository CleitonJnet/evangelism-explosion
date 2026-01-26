<?php

use App\Models\Course;
use App\Models\EventDate;
use App\Models\Section;
use App\Models\Training;
use App\Models\TrainingScheduleItem;
use App\Services\Schedule\TrainingScheduleGenerator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('generates lunch and breaks without overlaps', function () {
    $course = Course::factory()->create();

    Section::factory()->create(['course_id' => $course->id, 'order' => 1, 'duration' => 60, 'name' => 'Parte 1']);
    Section::factory()->create(['course_id' => $course->id, 'order' => 2, 'duration' => 60, 'name' => 'Parte 2']);
    Section::factory()->create(['course_id' => $course->id, 'order' => 3, 'duration' => 60, 'name' => 'Parte 3']);

    $training = Training::factory()->create(['course_id' => $course->id]);
    $training->eventDates()->delete();

    EventDate::query()->create([
        'training_id' => $training->id,
        'date' => '2026-02-10',
        'start_time' => '08:00:00',
        'end_time' => '17:00:00',
    ]);

    app(TrainingScheduleGenerator::class)->generate($training, 'FULL');

    $items = $training->scheduleItems()->orderBy('starts_at')->get();

    expect($items->where('type', 'MEAL'))->toHaveCount(1);
    expect($items->where('type', 'BREAK')->count())->toBeGreaterThanOrEqual(1);

    $items->groupBy('date')->each(function ($dayItems): void {
        $sorted = $dayItems->sortBy('starts_at')->values();

        for ($index = 0; $index < $sorted->count() - 1; $index++) {
            expect($sorted[$index]->ends_at->lte($sorted[$index + 1]->starts_at))->toBeTrue();
        }
    });
});

it('compresses sections proportionally when needed', function () {
    $course = Course::factory()->create();

    Section::factory()->create(['course_id' => $course->id, 'order' => 1, 'duration' => 60, 'name' => 'Parte 1']);
    Section::factory()->create(['course_id' => $course->id, 'order' => 2, 'duration' => 60, 'name' => 'Parte 2']);

    $training = Training::factory()->create(['course_id' => $course->id]);
    $training->eventDates()->delete();

    EventDate::query()->create([
        'training_id' => $training->id,
        'date' => '2026-02-10',
        'start_time' => '09:00:00',
        'end_time' => '10:40:00',
    ]);

    app(TrainingScheduleGenerator::class)->generate($training, 'FULL');

    $sections = $training->scheduleItems()->where('type', 'SECTION')->orderBy('starts_at')->get();

    expect($sections)->toHaveCount(2);
    expect($sections->sum('planned_duration_minutes'))->toBe(100);
    expect($sections->min('planned_duration_minutes'))->toBeGreaterThanOrEqual(45);
    expect($sections->last()->ends_at->format('H:i'))->toBe('10:40');
});

it('preserves teacher and locked items when regenerating auto only', function () {
    $course = Course::factory()->create();
    Section::factory()->create(['course_id' => $course->id, 'order' => 1, 'duration' => 60, 'name' => 'Parte 1']);

    $training = Training::factory()->create(['course_id' => $course->id]);
    $training->eventDates()->delete();

    EventDate::query()->create([
        'training_id' => $training->id,
        'date' => '2026-02-10',
        'start_time' => '08:00:00',
        'end_time' => '12:00:00',
    ]);

    $teacherItem = $training->scheduleItems()->create([
        'section_id' => null,
        'date' => '2026-02-10',
        'starts_at' => Carbon::parse('2026-02-10 08:00:00'),
        'ends_at' => Carbon::parse('2026-02-10 08:30:00'),
        'type' => 'OPENING',
        'title' => 'Abertura',
        'planned_duration_minutes' => 30,
        'suggested_duration_minutes' => null,
        'min_duration_minutes' => null,
        'origin' => 'TEACHER',
        'is_locked' => false,
        'status' => 'OK',
        'conflict_reason' => null,
        'meta' => null,
    ]);

    $lockedItem = $training->scheduleItems()->create([
        'section_id' => null,
        'date' => '2026-02-10',
        'starts_at' => Carbon::parse('2026-02-10 10:00:00'),
        'ends_at' => Carbon::parse('2026-02-10 10:30:00'),
        'type' => 'PRACTICE',
        'title' => 'Prática',
        'planned_duration_minutes' => 30,
        'suggested_duration_minutes' => null,
        'min_duration_minutes' => null,
        'origin' => 'AUTO',
        'is_locked' => true,
        'status' => 'OK',
        'conflict_reason' => null,
        'meta' => null,
    ]);

    $autoItem = $training->scheduleItems()->create([
        'section_id' => null,
        'date' => '2026-02-10',
        'starts_at' => Carbon::parse('2026-02-10 11:00:00'),
        'ends_at' => Carbon::parse('2026-02-10 11:30:00'),
        'type' => 'BREAK',
        'title' => 'Intervalo',
        'planned_duration_minutes' => 30,
        'suggested_duration_minutes' => null,
        'min_duration_minutes' => null,
        'origin' => 'AUTO',
        'is_locked' => false,
        'status' => 'OK',
        'conflict_reason' => null,
        'meta' => null,
    ]);

    app(TrainingScheduleGenerator::class)->generate($training, 'AUTO_ONLY');

    expect(TrainingScheduleItem::query()->whereKey($teacherItem->id)->exists())->toBeTrue();
    expect(TrainingScheduleItem::query()->whereKey($lockedItem->id)->exists())->toBeTrue();
    expect(TrainingScheduleItem::query()->whereKey($autoItem->id)->exists())->toBeFalse();
});

it('updates schedule items and marks conflicts when moved', function () {
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
        'starts_at' => Carbon::parse('2026-02-10 09:00:00'),
        'ends_at' => Carbon::parse('2026-02-10 10:00:00'),
        'type' => 'SECTION',
        'title' => 'Sessão 1',
        'planned_duration_minutes' => 60,
        'suggested_duration_minutes' => 60,
        'min_duration_minutes' => 45,
        'origin' => 'AUTO',
        'is_locked' => false,
        'status' => 'OK',
        'conflict_reason' => null,
        'meta' => null,
    ]);

    $second = $training->scheduleItems()->create([
        'section_id' => null,
        'date' => '2026-02-10',
        'starts_at' => Carbon::parse('2026-02-10 10:00:00'),
        'ends_at' => Carbon::parse('2026-02-10 11:00:00'),
        'type' => 'SECTION',
        'title' => 'Sessão 2',
        'planned_duration_minutes' => 60,
        'suggested_duration_minutes' => 60,
        'min_duration_minutes' => 45,
        'origin' => 'AUTO',
        'is_locked' => false,
        'status' => 'OK',
        'conflict_reason' => null,
        'meta' => null,
    ]);

    $this->withoutMiddleware([
        \Illuminate\Auth\Middleware\Authenticate::class,
        \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        \Illuminate\Auth\Middleware\Authorize::class,
    ])->patchJson(route('app.director.trainings.schedule-items.update', [
        'training' => $training->id,
        'item' => $first->id,
    ]), [
        'date' => '2026-02-10',
        'starts_at' => '2026-02-10 09:30:00',
    ])->assertSuccessful();

    $first->refresh();
    $second->refresh();

    expect($first->origin)->toBe('TEACHER');
    expect($first->status)->toBe('CONFLICT');
    expect($second->status)->toBe('CONFLICT');
});
