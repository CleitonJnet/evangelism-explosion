<?php

use App\Livewire\Pages\App\Teacher\Training\Schedule;
use App\Models\Course;
use App\Models\EventDate;
use App\Models\Section;
use App\Models\Training;
use App\Models\TrainingScheduleItem;
use App\Models\User;
use App\Services\Schedule\TrainingDayBlocksService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('toggles day blocks idempotently', function () {
    $teacher = User::factory()->create();
    $course = Course::factory()->create();
    $training = Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
    ]);

    $training->eventDates()->delete();

    EventDate::query()->create([
        'training_id' => $training->id,
        'date' => '2026-02-10',
        'start_time' => '08:00:00',
        'end_time' => '12:00:00',
    ]);

    $this->actingAs($teacher);

    $component = Livewire::test(Schedule::class, ['training' => $training]);

    $component->call('toggleDayBlock', '2026-02-10', 'welcome', true);

    expect(TrainingScheduleItem::query()
        ->where('training_id', $training->id)
        ->where('type', 'WELCOME')
        ->count())->toBe(1);

    $component->call('toggleDayBlock', '2026-02-10', 'welcome', true);

    expect(TrainingScheduleItem::query()
        ->where('training_id', $training->id)
        ->where('type', 'WELCOME')
        ->count())->toBe(1);

    $component->call('toggleDayBlock', '2026-02-10', 'welcome', false);

    expect(TrainingScheduleItem::query()
        ->where('training_id', $training->id)
        ->where('type', 'WELCOME')
        ->count())->toBe(0);
});

it('defaults meals to false and welcome/devotional to true', function () {
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

    $dayBlocks = app(TrainingDayBlocksService::class)->get($training->id);

    expect($dayBlocks['2026-02-10']['welcome'])->toBeTrue();
    expect($dayBlocks['2026-02-10']['devotional'])->toBeTrue();
    expect($dayBlocks['2026-02-10']['breakfast'])->toBeFalse();
    expect($dayBlocks['2026-02-10']['lunch'])->toBeFalse();
    expect($dayBlocks['2026-02-10']['snack'])->toBeFalse();
    expect($dayBlocks['2026-02-10']['dinner'])->toBeFalse();
});

it('inserts a break when snack is disabled during a long run', function () {
    $teacher = User::factory()->create();
    $course = Course::factory()->create();
    $training = Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
    ]);

    $training->eventDates()->delete();

    EventDate::query()->create([
        'training_id' => $training->id,
        'date' => '2026-02-10',
        'start_time' => '13:00:00',
        'end_time' => '18:00:00',
    ]);

    TrainingScheduleItem::factory()->create([
        'training_id' => $training->id,
        'section_id' => null,
        'date' => '2026-02-10',
        'starts_at' => Carbon::parse('2026-02-10 13:00:00'),
        'ends_at' => Carbon::parse('2026-02-10 14:00:00'),
        'planned_duration_minutes' => 60,
        'suggested_duration_minutes' => 60,
        'min_duration_minutes' => 45,
        'type' => 'SECTION',
        'position' => 1,
    ]);

    TrainingScheduleItem::factory()->create([
        'training_id' => $training->id,
        'section_id' => null,
        'date' => '2026-02-10',
        'starts_at' => Carbon::parse('2026-02-10 14:00:00'),
        'ends_at' => Carbon::parse('2026-02-10 15:00:00'),
        'planned_duration_minutes' => 60,
        'suggested_duration_minutes' => 60,
        'min_duration_minutes' => 45,
        'type' => 'SECTION',
        'position' => 2,
    ]);

    TrainingScheduleItem::factory()->create([
        'training_id' => $training->id,
        'section_id' => null,
        'date' => '2026-02-10',
        'starts_at' => Carbon::parse('2026-02-10 15:00:00'),
        'ends_at' => Carbon::parse('2026-02-10 16:00:00'),
        'planned_duration_minutes' => 60,
        'suggested_duration_minutes' => 60,
        'min_duration_minutes' => 45,
        'type' => 'SECTION',
        'position' => 3,
    ]);

    $this->actingAs($teacher);

    Livewire::test(Schedule::class, ['training' => $training])
        ->call('toggleDayBlock', '2026-02-10', 'snack', false)
        ->assertStatus(200);

    $break = TrainingScheduleItem::query()
        ->where('training_id', $training->id)
        ->where('type', 'BREAK')
        ->whereDate('date', '2026-02-10')
        ->first();

    expect($break)->not->toBeNull();
    expect($break?->meta['auto_reason'] ?? null)->toBe('snack_off');
});

it('removes invisible meal items during refresh', function () {
    $teacher = User::factory()->create();
    $course = Course::factory()->create();
    $training = Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
    ]);

    $training->eventDates()->delete();

    EventDate::query()->create([
        'training_id' => $training->id,
        'date' => '2026-02-10',
        'start_time' => '09:00:00',
        'end_time' => '11:00:00',
    ]);

    $breakfast = TrainingScheduleItem::factory()->create([
        'training_id' => $training->id,
        'section_id' => null,
        'date' => '2026-02-10',
        'starts_at' => Carbon::parse('2026-02-10 09:10:00'),
        'ends_at' => Carbon::parse('2026-02-10 09:40:00'),
        'planned_duration_minutes' => 30,
        'suggested_duration_minutes' => null,
        'min_duration_minutes' => null,
        'type' => 'MEAL',
        'origin' => 'AUTO',
        'meta' => ['anchor' => 'breakfast', 'subkind' => 'breakfast'],
        'position' => 1,
    ]);

    $this->actingAs($teacher);

    Livewire::test(Schedule::class, ['training' => $training])->assertStatus(200);

    expect(TrainingScheduleItem::query()->whereKey($breakfast->id)->exists())->toBeFalse();
});

it('suppresses snack break after deletion', function () {
    $teacher = User::factory()->create();
    $course = Course::factory()->create();
    $training = Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
    ]);

    $training->eventDates()->delete();

    EventDate::query()->create([
        'training_id' => $training->id,
        'date' => '2026-02-10',
        'start_time' => '13:00:00',
        'end_time' => '18:00:00',
    ]);

    $break = TrainingScheduleItem::factory()->create([
        'training_id' => $training->id,
        'section_id' => null,
        'date' => '2026-02-10',
        'starts_at' => Carbon::parse('2026-02-10 15:30:00'),
        'ends_at' => Carbon::parse('2026-02-10 15:40:00'),
        'planned_duration_minutes' => 10,
        'suggested_duration_minutes' => null,
        'min_duration_minutes' => null,
        'type' => 'BREAK',
        'origin' => 'AUTO',
        'meta' => ['auto_reason' => 'snack_off'],
        'position' => 1,
    ]);

    $this->actingAs($teacher);

    Livewire::test(Schedule::class, ['training' => $training])
        ->call('deleteBreak', $break->id)
        ->assertStatus(200);

    $training->refresh();

    expect(TrainingScheduleItem::query()->whereKey($break->id)->exists())->toBeFalse();
    expect($training->schedule_settings['overrides']['2026-02-10']['snack_break_suppressed'] ?? false)->toBeTrue();
});

it('adds and deletes breaks manually', function () {
    $teacher = User::factory()->create();
    $course = Course::factory()->create();
    $training = Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
    ]);

    $training->eventDates()->delete();

    EventDate::query()->create([
        'training_id' => $training->id,
        'date' => '2026-02-10',
        'start_time' => '09:00:00',
        'end_time' => '12:00:00',
    ]);

    $this->actingAs($teacher);

    $component = Livewire::test(Schedule::class, ['training' => $training]);

    $component->call('addBreak', '2026-02-10');

    $break = TrainingScheduleItem::query()
        ->where('training_id', $training->id)
        ->where('type', 'BREAK')
        ->first();

    expect($break)->not->toBeNull();

    $component->call('deleteBreak', $break->id);

    expect(TrainingScheduleItem::query()->whereKey($break->id)->exists())->toBeFalse();
});

it('blocks duration changes outside the 25 percent window', function () {
    $teacher = User::factory()->create();
    $course = Course::factory()->create();
    $section = Section::factory()->create(['course_id' => $course->id, 'duration' => 60]);
    $training = Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
    ]);

    $training->eventDates()->delete();

    EventDate::query()->create([
        'training_id' => $training->id,
        'date' => '2026-02-10',
        'start_time' => '09:00:00',
        'end_time' => '12:00:00',
    ]);

    $item = TrainingScheduleItem::factory()->create([
        'training_id' => $training->id,
        'section_id' => $section->id,
        'date' => '2026-02-10',
        'starts_at' => Carbon::parse('2026-02-10 09:00:00'),
        'ends_at' => Carbon::parse('2026-02-10 10:00:00'),
        'planned_duration_minutes' => 60,
        'suggested_duration_minutes' => 60,
        'min_duration_minutes' => 45,
        'type' => 'SECTION',
        'position' => 1,
    ]);

    $this->actingAs($teacher);

    Livewire::test(Schedule::class, ['training' => $training])
        ->set('durationInputs.'.$item->id, 90)
        ->call('applyDuration', $item->id)
        ->assertStatus(200);

    $item->refresh();

    expect($item->planned_duration_minutes)->toBe(60);
});
