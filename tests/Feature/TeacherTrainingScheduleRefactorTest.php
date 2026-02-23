<?php

use App\Livewire\Pages\App\Teacher\Training\EditEventDatesModal;
use App\Livewire\Pages\App\Teacher\Training\Schedule as TrainingScheduleComponent;
use App\Models\Course;
use App\Models\Role;
use App\Models\Section;
use App\Models\Training;
use App\Models\TrainingScheduleItem;
use App\Models\User;
use App\Services\Schedule\TrainingScheduleResetService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function makeTeacherForScheduleRefactorTest(): User
{
    $teacher = User::factory()->create();
    $teacherRole = Role::query()->firstOrCreate(['name' => 'Teacher']);
    $teacher->roles()->syncWithoutDetaching([$teacherRole->id]);

    return $teacher;
}

it('fills each day window and keeps each course section unique in the default schedule', function () {
    $teacher = makeTeacherForScheduleRefactorTest();
    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'welcome_duration_minutes' => 30,
    ]);

    $training->eventDates()->delete();
    $training->eventDates()->createMany([
        ['date' => '2026-03-10', 'start_time' => '08:00:00', 'end_time' => '18:00:00'],
        ['date' => '2026-03-11', 'start_time' => '08:00:00', 'end_time' => '17:00:00'],
    ]);

    $courseId = $training->course_id;

    Section::query()->where('course_id', $courseId)->delete();
    Section::factory()->create(['course_id' => $courseId, 'order' => 1, 'name' => 'Unidade A', 'duration' => '45 min']);
    Section::factory()->create(['course_id' => $courseId, 'order' => 2, 'name' => 'Unidade B', 'duration' => '60 min']);
    Section::factory()->create(['course_id' => $courseId, 'order' => 3, 'name' => 'Unidade C', 'duration' => '1h30']);
    Section::factory()->create(['course_id' => $courseId, 'order' => 4, 'name' => 'Unidade D', 'duration' => '35 min']);
    Section::factory()->create(['course_id' => $courseId, 'order' => 5, 'name' => 'Unidade E', 'duration' => '50']);

    app(TrainingScheduleResetService::class)->resetFull($training->id);

    $training->refresh()->load([
        'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
        'scheduleItems' => fn ($query) => $query->orderBy('date')->orderBy('position'),
    ]);

    foreach ($training->eventDates as $eventDate) {
        $dateKey = Carbon::parse((string) $eventDate->date)->format('Y-m-d');
        $lastItem = $training->scheduleItems
            ->filter(fn (TrainingScheduleItem $item): bool => $item->date?->format('Y-m-d') === $dateKey)
            ->sortBy('position')
            ->last();

        expect($lastItem)->not->toBeNull();
        expect($lastItem?->ends_at?->format('H:i:s'))->toBe((string) $eventDate->end_time);
    }

    $sectionItems = $training->scheduleItems
        ->filter(fn (TrainingScheduleItem $item): bool => $item->type === 'SECTION' && $item->section_id !== null)
        ->values();

    expect($sectionItems->pluck('section_id')->unique()->count())->toBe($sectionItems->count());

    $sectionItems->each(function (TrainingScheduleItem $item): void {
        $suggested = (int) ($item->suggested_duration_minutes ?? 0);

        if ($suggested <= 0) {
            return;
        }

        $min = (int) ceil($suggested * 0.8);
        $min = min(120, $min);
        $max = min(120, (int) floor($suggested * 1.2));
        $max = max($min, $max);

        expect((int) $item->planned_duration_minutes)->toBeGreaterThanOrEqual($min);
        expect((int) $item->planned_duration_minutes)->toBeLessThanOrEqual($max);
        expect((int) $item->planned_duration_minutes)->toBeLessThanOrEqual(120);
    });
});

it('keeps section duration from catalog when the residual gap is acceptable', function () {
    $teacher = makeTeacherForScheduleRefactorTest();
    $course = Course::factory()->create();

    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'course_id' => $course->id,
        'welcome_duration_minutes' => 30,
    ]);

    $training->eventDates()->delete();
    $training->eventDates()->create([
        'date' => '2026-08-10',
        'start_time' => '06:00:00',
        'end_time' => '10:30:00',
    ]);

    Section::query()->where('course_id', $course->id)->delete();

    foreach (range(1, 3) as $order) {
        Section::factory()->create([
            'course_id' => $course->id,
            'order' => $order,
            'name' => 'Secao Base '.$order,
            'duration' => '60 min',
        ]);
    }

    app(TrainingScheduleResetService::class)->resetFull($training->id);

    $sectionItems = TrainingScheduleItem::query()
        ->where('training_id', $training->id)
        ->whereDate('date', '2026-08-10')
        ->where('type', 'SECTION')
        ->whereNotNull('section_id')
        ->orderBy('position')
        ->get();

    expect($sectionItems)->toHaveCount(3);

    $sectionItems->each(function (TrainingScheduleItem $item): void {
        expect((int) $item->planned_duration_minutes)->toBe(60);
        expect((int) $item->suggested_duration_minutes)->toBe(60);
    });
});

it('does not regenerate schedule when teacher saves identical event dates', function () {
    $teacher = makeTeacherForScheduleRefactorTest();
    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
    ]);

    $training->eventDates()->delete();
    $training->eventDates()->create([
        'date' => '2026-04-15',
        'start_time' => '09:00:00',
        'end_time' => '17:00:00',
    ]);

    $item = TrainingScheduleItem::query()->create([
        'training_id' => $training->id,
        'section_id' => null,
        'date' => '2026-04-15',
        'starts_at' => Carbon::parse('2026-04-15 09:00:00'),
        'ends_at' => Carbon::parse('2026-04-15 10:51:00'),
        'type' => 'SECTION',
        'title' => 'Sessao Personalizada',
        'position' => 1,
        'planned_duration_minutes' => 111,
        'suggested_duration_minutes' => 111,
        'min_duration_minutes' => 89,
        'origin' => 'TEACHER',
        'status' => 'OK',
        'conflict_reason' => null,
        'meta' => ['fixed_duration' => true],
    ]);

    Livewire::actingAs($teacher)
        ->test(EditEventDatesModal::class, ['trainingId' => $training->id])
        ->set('eventDates', [
            ['date' => '2026-04-15', 'start_time' => '09:00', 'end_time' => '17:00'],
        ])
        ->call('save');

    expect(TrainingScheduleItem::query()->whereKey($item->id)->exists())->toBeTrue();
    expect((int) TrainingScheduleItem::query()->find($item->id)->planned_duration_minutes)->toBe(111);
});

it('inserts auto breaks without happening before 70 minutes and keeps pauses spaced', function () {
    $teacher = makeTeacherForScheduleRefactorTest();
    $course = Course::factory()->create();
    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'course_id' => $course->id,
        'schedule_settings' => [
            'meals' => [
                'breakfast' => ['enabled' => false, 'duration_minutes' => 30],
                'lunch' => ['enabled' => false, 'duration_minutes' => 60],
                'afternoon_snack' => ['enabled' => false, 'duration_minutes' => 30],
                'dinner' => ['enabled' => false, 'duration_minutes' => 60, 'substitute_snack' => false],
            ],
        ],
    ]);

    $training->eventDates()->delete();
    $training->eventDates()->create([
        'date' => '2026-05-20',
        'start_time' => '13:00:00',
        'end_time' => '23:00:00',
    ]);

    $courseId = $course->id;
    Section::query()->where('course_id', $courseId)->delete();

    foreach (range(1, 8) as $order) {
        Section::factory()->create([
            'course_id' => $courseId,
            'order' => $order,
            'name' => 'Modulo '.$order,
            'duration' => '60 min',
        ]);
    }

    app(TrainingScheduleResetService::class)->resetFull($training->id);

    $items = TrainingScheduleItem::query()
        ->where('training_id', $training->id)
        ->whereDate('date', '2026-05-20')
        ->orderBy('position')
        ->get();

    $autoBreaks = $items
        ->filter(function (TrainingScheduleItem $item): bool {
            $meta = is_array($item->meta) ? $item->meta : [];

            return $item->type === 'BREAK'
                && $item->origin === 'AUTO'
                && (($meta['anchor'] ?? null) === 'break');
        })
        ->values();
    expect($autoBreaks->count())->toBeGreaterThanOrEqual(1);

    $minutesSincePause = 0;
    $lastAutoBreakStart = null;

    foreach ($items as $item) {
        $isPause = in_array($item->type, ['BREAK', 'MEAL', 'WELCOME'], true);
        $countsAsClass = ! $isPause;

        if ($countsAsClass) {
            $minutesSincePause += (int) $item->planned_duration_minutes;
        }

        $meta = is_array($item->meta) ? $item->meta : [];

        if ($item->type === 'BREAK' && $item->origin === 'AUTO' && (($meta['anchor'] ?? null) === 'break')) {
            if ($minutesSincePause > 0) {
                expect($minutesSincePause)->toBeGreaterThanOrEqual(70);
                expect($minutesSincePause)->toBeLessThanOrEqual(140);
            }

            if ($lastAutoBreakStart !== null) {
                $distance = $lastAutoBreakStart->diffInMinutes($item->starts_at);
                expect($distance)->toBeGreaterThanOrEqual(60);
            }

            $lastAutoBreakStart = $item->starts_at?->copy();
        }

        if ($isPause) {
            $minutesSincePause = 0;
        }
    }
});

it('inserts a break when a default period would exceed three consecutive sections', function () {
    $teacher = makeTeacherForScheduleRefactorTest();
    $course = Course::factory()->create();

    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'course_id' => $course->id,
    ]);

    $training->eventDates()->delete();
    $training->eventDates()->create([
        'date' => '2026-05-25',
        'start_time' => '08:40:00',
        'end_time' => '12:00:00',
    ]);

    Section::query()->where('course_id', $course->id)->delete();

    foreach (range(1, 4) as $order) {
        Section::factory()->create([
            'course_id' => $course->id,
            'order' => $order,
            'name' => 'Secao '.$order,
            'duration' => '30 min',
        ]);
    }

    app(TrainingScheduleResetService::class)->resetFull($training->id);

    $items = TrainingScheduleItem::query()
        ->where('training_id', $training->id)
        ->whereDate('date', '2026-05-25')
        ->orderBy('position')
        ->get();

    $maxConsecutiveSections = 0;
    $currentRun = 0;

    foreach ($items as $item) {
        if ($item->type === 'SECTION') {
            $currentRun++;
            $maxConsecutiveSections = max($maxConsecutiveSections, $currentRun);

            continue;
        }

        if (in_array($item->type, ['BREAK', 'MEAL', 'WELCOME', 'DEVOTIONAL'], true)) {
            $currentRun = 0;
        }
    }

    $autoBreaks = $items->filter(function (TrainingScheduleItem $item): bool {
        $meta = is_array($item->meta) ? $item->meta : [];

        return $item->type === 'BREAK'
            && $item->origin === 'AUTO'
            && (($meta['anchor'] ?? null) === 'break');
    });

    expect($autoBreaks->count())->toBeGreaterThan(0);
    expect($maxConsecutiveSections)->toBeLessThanOrEqual(3);
});

it('sets default meal switches according to event day period windows', function () {
    $teacher = makeTeacherForScheduleRefactorTest();
    $course = Course::factory()->create();

    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'course_id' => $course->id,
    ]);

    $training->eventDates()->delete();
    $training->eventDates()->createMany([
        ['date' => '2026-06-01', 'start_time' => '08:00:00', 'end_time' => '17:00:00'],
        ['date' => '2026-06-02', 'start_time' => '12:00:00', 'end_time' => '17:50:00'],
        ['date' => '2026-06-03', 'start_time' => '14:00:00', 'end_time' => '21:00:00'],
        ['date' => '2026-06-04', 'start_time' => '08:00:00', 'end_time' => '21:00:00'],
        ['date' => '2026-06-05', 'start_time' => '14:00:00', 'end_time' => '21:30:00'],
    ]);

    Section::factory()->create([
        'course_id' => $course->id,
        'order' => 1,
        'name' => 'Abertura',
        'duration' => '60 min',
    ]);

    app(TrainingScheduleResetService::class)->resetFull($training->id);
    $training->refresh();

    $dayBlocks = $training->schedule_settings['day_blocks'] ?? [];

    expect((bool) data_get($dayBlocks, '2026-06-01.lunch'))->toBeTrue();
    expect((bool) data_get($dayBlocks, '2026-06-01.snack'))->toBeTrue();
    expect((bool) data_get($dayBlocks, '2026-06-02.snack'))->toBeTrue();
    expect((bool) data_get($dayBlocks, '2026-06-02.dinner'))->toBeFalse();
    expect((bool) data_get($dayBlocks, '2026-06-03.dinner'))->toBeFalse();
    expect((bool) data_get($dayBlocks, '2026-06-04.snack'))->toBeTrue();
    expect((bool) data_get($dayBlocks, '2026-06-04.dinner'))->toBeFalse();
    expect((bool) data_get($dayBlocks, '2026-06-05.dinner'))->toBeTrue();
});

it('positions default meal slots with fixed start times and expected durations', function () {
    $teacher = makeTeacherForScheduleRefactorTest();
    $course = Course::factory()->create();

    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'course_id' => $course->id,
    ]);

    $training->eventDates()->delete();
    $training->eventDates()->createMany([
        ['date' => '2026-07-01', 'start_time' => '08:00:00', 'end_time' => '17:30:00'],
        ['date' => '2026-07-02', 'start_time' => '15:00:00', 'end_time' => '18:00:00'],
        ['date' => '2026-07-03', 'start_time' => '14:00:00', 'end_time' => '21:30:00'],
    ]);

    Section::factory()->create([
        'course_id' => $course->id,
        'order' => 1,
        'name' => 'Sessao Base',
        'duration' => '60 min',
    ]);

    app(TrainingScheduleResetService::class)->resetFull($training->id);

    $items = TrainingScheduleItem::query()
        ->where('training_id', $training->id)
        ->where('type', 'MEAL')
        ->get()
        ->keyBy(fn (TrainingScheduleItem $item): string => $item->date?->format('Y-m-d').'|'.((is_array($item->meta) ? ($item->meta['anchor'] ?? null) : null) ?? ''));

    $lunch = $items->get('2026-07-01|lunch');
    $snack = $items->get('2026-07-02|afternoon_snack');
    $dinner = $items->get('2026-07-03|dinner');

    expect($lunch)->not->toBeNull();
    expect($lunch?->starts_at?->format('H:i:s'))->toBe('12:00:00');
    expect($lunch?->ends_at?->format('H:i:s'))->toBe('13:00:00');

    expect($snack)->not->toBeNull();
    expect($snack?->starts_at?->format('H:i:s'))->toBe('15:15:00');
    expect($snack?->ends_at?->format('H:i:s'))->toBe('15:45:00');

    expect($dinner)->not->toBeNull();
    expect($dinner?->starts_at?->format('H:i:s'))->toBe('18:00:00');
    expect($dinner?->ends_at?->format('H:i:s'))->toBe('19:00:00');
});

it('increments session duration using action button', function () {
    $teacher = makeTeacherForScheduleRefactorTest();
    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
    ]);

    $training->eventDates()->delete();
    $training->eventDates()->create([
        'date' => '2026-08-10',
        'start_time' => '08:00:00',
        'end_time' => '08:31:00',
    ]);

    $item = TrainingScheduleItem::query()->create([
        'training_id' => $training->id,
        'section_id' => null,
        'date' => '2026-08-10',
        'starts_at' => Carbon::parse('2026-08-10 08:00:00'),
        'ends_at' => Carbon::parse('2026-08-10 08:30:00'),
        'type' => 'SECTION',
        'title' => 'Sessão Base',
        'position' => 1,
        'planned_duration_minutes' => 30,
        'suggested_duration_minutes' => 30,
        'min_duration_minutes' => 24,
        'origin' => 'AUTO',
        'status' => 'OK',
        'conflict_reason' => null,
        'meta' => null,
    ]);

    Livewire::actingAs($teacher)
        ->test(TrainingScheduleComponent::class, ['training' => $training])
        ->call('incrementDuration', $item->id);

    expect((int) $item->fresh()->planned_duration_minutes)->toBe(31);
});
