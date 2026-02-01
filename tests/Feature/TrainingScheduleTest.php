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

    $training = Training::factory()->create([
        'course_id' => $course->id,
        'schedule_settings' => [
            'days' => [
                '2026-02-10' => [
                    'welcome_enabled' => false,
                ],
            ],
        ],
    ]);
    $training->eventDates()->delete();

    EventDate::query()->create([
        'training_id' => $training->id,
        'date' => '2026-02-10',
        'start_time' => '08:00:00',
        'end_time' => '17:00:00',
    ]);

    app(TrainingScheduleGenerator::class)->generate($training, 'FULL');

    $items = $training->scheduleItems()->orderBy('starts_at')->get();

    expect($items->where('type', 'MEAL')->where('title', 'Almoço'))->toHaveCount(1);
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

it('adds dinner when the day includes 18:00', function () {
    $course = Course::factory()->create();

    Section::factory()->create(['course_id' => $course->id, 'order' => 1, 'duration' => 60, 'name' => 'Parte 1']);

    $training = Training::factory()->create([
        'course_id' => $course->id,
        'schedule_settings' => [
            'days' => [
                '2026-02-10' => [
                    'welcome_enabled' => false,
                ],
            ],
        ],
    ]);
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

    expect($firstDayDinner)->not->toBeNull();
    expect($secondDayDinner)->not->toBeNull();
    expect($firstDayDinner?->starts_at->format('H:i'))->toBe('18:00');
    expect($secondDayDinner?->starts_at->format('H:i'))->toBe('18:00');
});

it('allows dinner to be swapped for snack per day', function () {
    $course = Course::factory()->create();

    Section::factory()->create(['course_id' => $course->id, 'order' => 1, 'duration' => 60, 'name' => 'Parte 1']);

    $training = Training::factory()->create([
        'course_id' => $course->id,
        'schedule_settings' => [
            'days' => [
                '2026-02-10' => [
                    'meals' => [
                        'dinner' => [
                            'enabled' => true,
                            'duration_minutes' => 60,
                            'substitute_snack' => true,
                        ],
                    ],
                ],
                '2026-02-11' => [
                    'meals' => [
                        'dinner' => [
                            'enabled' => true,
                            'duration_minutes' => 60,
                            'substitute_snack' => false,
                        ],
                    ],
                ],
            ],
        ],
    ]);
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

    $firstDayMeal = $training->scheduleItems()
        ->where('type', 'MEAL')
        ->whereDate('date', '2026-02-10')
        ->first();

    $secondDayMeal = $training->scheduleItems()
        ->where('type', 'MEAL')
        ->whereDate('date', '2026-02-11')
        ->first();

    expect($firstDayMeal?->title)->toBe('Lanche');
    expect($secondDayMeal?->title)->toBe('Jantar');
});

it('fills the remaining minutes even below minimum on the last slot of the day', function () {
    $course = Course::factory()->create();

    Section::factory()->create(['course_id' => $course->id, 'order' => 1, 'duration' => 60, 'name' => 'Parte 1']);

    $training = Training::factory()->create(['course_id' => $course->id]);
    $training->eventDates()->delete();
    $training->update([
        'schedule_settings' => [
            'meals' => [
                'breakfast' => ['enabled' => false],
                'lunch' => ['enabled' => false],
                'afternoon_snack' => ['enabled' => false],
                'dinner' => ['enabled' => false],
            ],
            'days' => [
                '2026-02-10' => [
                    'welcome_enabled' => false,
                ],
            ],
        ],
    ]);
    EventDate::query()->create([
        'training_id' => $training->id,
        'date' => '2026-02-10',
        'start_time' => '08:00:00',
        'end_time' => '08:30:00',
    ]);

    EventDate::query()->create([
        'training_id' => $training->id,
        'date' => '2026-02-11',
        'start_time' => '09:00:00',
        'end_time' => '10:00:00',
    ]);

    app(TrainingScheduleGenerator::class)->generate($training, 'FULL');

    $firstDaySection = $training->scheduleItems()
        ->where('type', 'SECTION')
        ->whereDate('date', '2026-02-10')
        ->first();

    $secondDaySection = $training->scheduleItems()
        ->where('type', 'SECTION')
        ->whereDate('date', '2026-02-11')
        ->first();

    expect($firstDaySection)->toBeNull();
    expect($secondDaySection)->not->toBeNull();
    expect($secondDaySection?->planned_duration_minutes)->toBe(60);
});

it('stretches sections proportionally to fill the day without splitting across days', function () {
    $course = Course::factory()->create();

    Section::factory()->create(['course_id' => $course->id, 'order' => 1, 'duration' => 60, 'name' => 'Parte 1']);
    Section::factory()->create(['course_id' => $course->id, 'order' => 2, 'duration' => 30, 'name' => 'Parte 2']);

    $training = Training::factory()->create(['course_id' => $course->id]);
    $training->eventDates()->delete();
    $training->update([
        'schedule_settings' => [
            'meals' => [
                'breakfast' => ['enabled' => false],
                'lunch' => ['enabled' => false],
                'afternoon_snack' => ['enabled' => false],
                'dinner' => ['enabled' => false],
            ],
            'days' => [
                '2026-02-10' => [
                    'welcome_enabled' => false,
                ],
            ],
        ],
    ]);

    EventDate::query()->create([
        'training_id' => $training->id,
        'date' => '2026-02-10',
        'start_time' => '08:00:00',
        'end_time' => '10:00:00',
    ]);

    app(TrainingScheduleGenerator::class)->generate($training, 'FULL');

    $sections = $training->scheduleItems()
        ->where('type', 'SECTION')
        ->whereDate('date', '2026-02-10')
        ->get();
    $durations = $sections->pluck('planned_duration_minutes')->sort()->values()->all();

    expect($sections)->toHaveCount(2);
    expect($durations)->toBe([35, 70]);
});

it('splits sessions after lunch and inserts a single break after the first block', function () {
    $course = Course::factory()->create();

    Section::factory()->create(['course_id' => $course->id, 'order' => 1, 'duration' => 120, 'name' => 'Parte 1']);

    $training = Training::factory()->create(['course_id' => $course->id]);
    $training->eventDates()->delete();

    EventDate::query()->create([
        'training_id' => $training->id,
        'date' => '2026-02-10',
        'start_time' => '11:30:00',
        'end_time' => '17:00:00',
    ]);

    app(TrainingScheduleGenerator::class)->generate($training, 'FULL');

    $sections = $training->scheduleItems()
        ->where('type', 'SECTION')
        ->whereDate('date', '2026-02-10')
        ->get()
        ->values();

    $breaks = $training->scheduleItems()
        ->where('type', 'BREAK')
        ->whereDate('date', '2026-02-10')
        ->get()
        ->values();

    expect($sections)->toHaveCount(2);
    expect($sections->max('planned_duration_minutes'))->toBeLessThanOrEqual(80);
    expect($sections->sortBy('planned_duration_minutes')->pluck('planned_duration_minutes')->values()->all())->toBe([40, 80]);
    expect($breaks)->toHaveCount(1);
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

    $itemId = TrainingScheduleItem::query()
        ->where('training_id', $training->id)
        ->where('title', 'Sessão extra')
        ->value('id');

    expect($itemId)->not->toBeNull();

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

    expect($ordered->first()?->type)->toBe('WELCOME');

    $secondItem = $ordered->get(1);
    $thirdItem = $ordered->get(2);

    expect($secondItem?->title)->toBe('Segunda');
    expect($secondItem?->starts_at->format('H:i'))->toBe('09:30');
    expect($secondItem?->ends_at->format('H:i'))->toBe('10:00');
    expect($thirdItem?->title)->toBe('Primeira');
    expect($thirdItem?->starts_at->format('H:i'))->toBe('10:00');
    expect($thirdItem?->ends_at->format('H:i'))->toBe('11:00');
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

    app(TrainingScheduleGenerator::class)->generate($training, 'AUTO_ONLY');

    expect(TrainingScheduleItem::query()->where('training_id', $training->id)->where('title', 'Abertura')->exists())
        ->toBeTrue();
    expect(TrainingScheduleItem::query()->where('training_id', $training->id)->where('title', 'Prática')->where('is_locked', true)->exists())
        ->toBeTrue();
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

    $ordered = $training->scheduleItems()
        ->whereDate('date', '2026-02-10')
        ->orderBy('starts_at')
        ->get();

    expect($ordered->first()?->type)->toBe('WELCOME');

    $sessions = $ordered->where('type', 'SECTION')->values();

    expect($sessions->first()?->title)->toBe('Sessão 1');
    expect($sessions->first()?->starts_at->format('H:i'))->toBe('09:30');
    expect($sessions->first()?->ends_at->format('H:i'))->toBe('10:30');
    expect($sessions->last()?->title)->toBe('Sessão 2');
    expect($sessions->last()?->starts_at->format('H:i'))->toBe('10:45');
    expect($sessions->last()?->ends_at->format('H:i'))->toBe('11:45');
});
