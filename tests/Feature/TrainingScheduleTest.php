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

it('adds a welcome period on the first day', function () {
    $course = Course::factory()->create();

    Section::factory()->create(['course_id' => $course->id, 'order' => 1, 'duration' => 60, 'name' => 'Parte 1']);

    $training = Training::factory()->create([
        'course_id' => $course->id,
        'welcome_duration_minutes' => 45,
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
        'start_time' => '08:00:00',
        'end_time' => '12:00:00',
    ]);

    app(TrainingScheduleGenerator::class)->generate($training, 'FULL');

    $welcomeItems = $training->scheduleItems()->where('type', 'WELCOME')->orderBy('starts_at')->get();
    $firstItem = $training->scheduleItems()->orderBy('starts_at')->first();

    expect($welcomeItems)->toHaveCount(1);
    expect($firstItem)->not->toBeNull();
    expect($firstItem?->type)->toBe('WELCOME');
    expect($welcomeItems->first()?->date->format('Y-m-d'))->toBe('2026-02-10');
    expect($welcomeItems->first()?->starts_at->format('H:i'))->toBe('08:00');
    expect($welcomeItems->first()?->ends_at->format('H:i'))->toBe('08:45');
    expect($welcomeItems->first()?->planned_duration_minutes)->toBe(45);
});

it('adds a devotional period on days after the first', function () {
    $course = Course::factory()->create();

    Section::factory()->create(['course_id' => $course->id, 'order' => 1, 'duration' => 60, 'name' => 'Parte 1']);

    $training = Training::factory()->create([
        'course_id' => $course->id,
        'welcome_duration_minutes' => 30,
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
        'start_time' => '08:00:00',
        'end_time' => '12:00:00',
    ]);

    app(TrainingScheduleGenerator::class)->generate($training, 'FULL');

    $devotional = $training->scheduleItems()
        ->where('title', 'Devocional')
        ->whereDate('date', '2026-02-11')
        ->first();

    $firstItemSecondDay = $training->scheduleItems()
        ->whereDate('date', '2026-02-11')
        ->orderBy('starts_at')
        ->first();

    expect($devotional)->not->toBeNull();
    expect($devotional?->starts_at->format('H:i'))->toBe('08:00');
    expect($devotional?->ends_at->format('H:i'))->toBe('08:30');
    expect($firstItemSecondDay?->title)->toBe('Devocional');
});

it('adds dinner after 18:00 except on the first day', function () {
    $course = Course::factory()->create();

    Section::factory()->create(['course_id' => $course->id, 'order' => 1, 'duration' => 60, 'name' => 'Parte 1']);

    $training = Training::factory()->create(['course_id' => $course->id]);
    $training->eventDates()->delete();

    EventDate::query()->create([
        'training_id' => $training->id,
        'date' => '2026-02-10',
        'start_time' => '17:00:00',
        'end_time' => '19:30:00',
    ]);

    EventDate::query()->create([
        'training_id' => $training->id,
        'date' => '2026-02-11',
        'start_time' => '17:00:00',
        'end_time' => '19:30:00',
    ]);

    app(TrainingScheduleGenerator::class)->generate($training, 'FULL');

    $firstDayDinner = $training->scheduleItems()
        ->where('type', 'MEAL')
        ->where('title', 'Jantar')
        ->whereDate('date', '2026-02-10')
        ->first();

    $secondDayDinner = $training->scheduleItems()
        ->where('type', 'MEAL')
        ->where('title', 'Jantar')
        ->whereDate('date', '2026-02-11')
        ->first();

    expect($firstDayDinner)->toBeNull();
    expect($secondDayDinner)->not->toBeNull();
    expect($secondDayDinner?->starts_at->format('H:i'))->toBe('18:00');
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
        'end_time' => '11:10:00',
    ]);

    app(TrainingScheduleGenerator::class)->generate($training, 'FULL');

    $sections = $training->scheduleItems()->where('type', 'SECTION')->orderBy('starts_at')->get();

    expect($sections)->toHaveCount(2);
    expect($sections->sum('planned_duration_minutes'))->toBe(100);
    expect($sections->min('planned_duration_minutes'))->toBeGreaterThanOrEqual(45);
    expect($sections->last()->ends_at->format('H:i'))->toBe('11:10');
});

it('never ends the day with a break item', function () {
    $course = Course::factory()->create();

    Section::factory()->create(['course_id' => $course->id, 'order' => 1, 'duration' => 90, 'name' => 'Parte 1']);
    Section::factory()->create(['course_id' => $course->id, 'order' => 2, 'duration' => 30, 'name' => 'Parte 2']);

    $training = Training::factory()->create(['course_id' => $course->id]);
    $training->eventDates()->delete();

    EventDate::query()->create([
        'training_id' => $training->id,
        'date' => '2026-02-10',
        'start_time' => '08:00:00',
        'end_time' => '09:45:00',
    ]);

    app(TrainingScheduleGenerator::class)->generate($training, 'FULL');

    $lastItem = $training->scheduleItems()->orderBy('starts_at')->get()->last();

    expect($lastItem)->not->toBeNull();
    expect($lastItem?->type)->not->toBe('BREAK');
});

it('creates and removes schedule items', function () {
    $course = Course::factory()->create();
    $training = Training::factory()->create(['course_id' => $course->id]);
    $training->eventDates()->delete();

    EventDate::query()->create([
        'training_id' => $training->id,
        'date' => '2026-02-10',
        'start_time' => '09:00:00',
        'end_time' => '12:00:00',
    ]);

    $response = $this->withoutMiddleware([
        \Illuminate\Auth\Middleware\Authenticate::class,
        \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        \Illuminate\Auth\Middleware\Authorize::class,
    ])->postJson(route('app.director.trainings.schedule-items.store', [
        'training' => $training->id,
    ]), [
        'date' => '2026-02-10',
        'starts_at' => '2026-02-10 09:00:00',
        'planned_duration_minutes' => 30,
        'title' => 'Sessão extra',
        'type' => 'SECTION',
    ]);

    $response->assertSuccessful();
    $itemId = $response->json('item.id');

    expect(TrainingScheduleItem::query()->whereKey($itemId)->exists())->toBeTrue();

    $deleteResponse = $this->withoutMiddleware([
        \Illuminate\Auth\Middleware\Authenticate::class,
        \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        \Illuminate\Auth\Middleware\Authorize::class,
    ])->deleteJson(route('app.director.trainings.schedule-items.destroy', [
        'training' => $training->id,
        'item' => $itemId,
    ]));

    $deleteResponse->assertSuccessful();

    expect(TrainingScheduleItem::query()->whereKey($itemId)->exists())->toBeFalse();
});

it('reflows times when moving a schedule item', function () {
    $course = Course::factory()->create();
    $training = Training::factory()->create(['course_id' => $course->id]);
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
        'title' => 'Primeira',
        'planned_duration_minutes' => 60,
        'suggested_duration_minutes' => null,
        'min_duration_minutes' => null,
        'origin' => 'TEACHER',
        'is_locked' => false,
        'status' => 'OK',
        'conflict_reason' => null,
        'meta' => null,
    ]);

    $second = $training->scheduleItems()->create([
        'section_id' => null,
        'date' => '2026-02-10',
        'starts_at' => Carbon::parse('2026-02-10 10:00:00'),
        'ends_at' => Carbon::parse('2026-02-10 10:30:00'),
        'type' => 'SECTION',
        'title' => 'Segunda',
        'planned_duration_minutes' => 30,
        'suggested_duration_minutes' => null,
        'min_duration_minutes' => null,
        'origin' => 'TEACHER',
        'is_locked' => false,
        'status' => 'OK',
        'conflict_reason' => null,
        'meta' => null,
    ]);

    $response = $this->withoutMiddleware([
        \Illuminate\Auth\Middleware\Authenticate::class,
        \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        \Illuminate\Auth\Middleware\Authorize::class,
    ])->patchJson(route('app.director.trainings.schedule-items.update', [
        'training' => $training->id,
        'item' => $second->id,
    ]), [
        'date' => '2026-02-10',
        'starts_at' => '2026-02-10 09:00:00',
    ]);

    $response->assertSuccessful();

    $ordered = $training->scheduleItems()
        ->whereDate('date', '2026-02-10')
        ->orderBy('starts_at')
        ->get();

    expect($ordered)->toHaveCount(2);
    expect($ordered->first()?->id)->toBe($second->id);
    expect($ordered->first()?->starts_at->format('H:i'))->toBe('09:00');
    expect($ordered->first()?->ends_at->format('H:i'))->toBe('09:30');
    expect($ordered->last()?->id)->toBe($first->id);
    expect($ordered->last()?->starts_at->format('H:i'))->toBe('09:30');
    expect($ordered->last()?->ends_at->format('H:i'))->toBe('10:30');
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
    expect($first->status)->toBe('OK');
    expect($second->status)->toBe('OK');
    expect($first->starts_at->format('H:i'))->toBe('09:00');
    expect($first->ends_at->format('H:i'))->toBe('10:00');
    expect($second->starts_at->format('H:i'))->toBe('10:00');
    expect($second->ends_at->format('H:i'))->toBe('11:00');
});
