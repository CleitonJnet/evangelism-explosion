<?php

use App\Livewire\Pages\App\Teacher\Training\Schedule;
use App\Models\Course;
use App\Models\EventDate;
use App\Models\Role;
use App\Models\Section;
use App\Models\Training;
use App\Models\TrainingScheduleItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('shows the training schedule page for teachers', function () {
    $teacher = User::factory()->create();
    $role = Role::query()->create(['name' => 'Teacher']);
    $course = Course::factory()->create(['name' => 'Treinamento Alpha']);
    $training = Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
    ]);
    $eventDate = $training->eventDates->first();

    $scheduleItem = TrainingScheduleItem::factory()->create([
        'training_id' => $training->id,
        'date' => $eventDate?->date,
        'starts_at' => Carbon::parse($eventDate?->date.' 09:00:00'),
        'ends_at' => Carbon::parse($eventDate?->date.' 10:00:00'),
    ]);
    $dayStart = Carbon::parse($eventDate?->date.' '.$eventDate?->start_time)->format('Y-m-d H:i:s');

    $teacher->roles()->attach($role->id);

    $this->actingAs($teacher)
        ->get(route('app.teacher.trainings.schedule', $training))
        ->assertSuccessful()
        ->assertSeeLivewire(\App\Livewire\Pages\App\Teacher\Training\Schedule::class)
        ->assertSee('Programação do treinamento')
        ->assertSee('Treinamento Alpha')
        ->assertSee('Teacher')
        ->assertSee('Training')
        ->assertSee('Programação')
        ->assertSee('Arrastar para reordenar')
        ->assertSee('js-schedule-day-list', false)
        ->assertSee('data-date-key="'.$eventDate?->date.'"', false)
        ->assertSee('data-day-start="'.$dayStart.'"', false)
        ->assertSee('js-schedule-item', false)
        ->assertSee('data-item-id="', false)
        ->assertSee('data-starts-at="', false)
        ->assertSee('data-ends-at="', false)
        ->assertSee('js-drag-handle', false);
});

it('shows details before edit on the training edit breadcrumb', function () {
    $teacher = User::factory()->create();
    $role = Role::query()->create(['name' => 'Teacher']);
    $course = Course::factory()->create();
    $training = Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
    ]);

    $teacher->roles()->attach($role->id);

    $this->actingAs($teacher)
        ->get(route('app.teacher.trainings.edit', $training))
        ->assertSuccessful()
        ->assertSee('Detalhes')
        ->assertSee('Editar');
});

it('does not expose create, delete, or lock actions on the teacher schedule component', function () {
    expect(method_exists(Schedule::class, 'openCreate'))->toBeFalse();
    expect(method_exists(Schedule::class, 'createItem'))->toBeFalse();
    expect(method_exists(Schedule::class, 'deleteItem'))->toBeFalse();
    expect(method_exists(Schedule::class, 'toggleLock'))->toBeFalse();
});

it('loads day blocks from schedule settings', function () {
    $teacher = User::factory()->create();
    $course = Course::factory()->create();
    $training = Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
        'schedule_settings' => [
            'day_blocks' => [
                '2026-02-10' => [
                    'snack' => false,
                ],
            ],
            'overrides' => [],
        ],
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

    $this->actingAs($teacher);

    Livewire::test(Schedule::class, ['training' => $training])
        ->assertStatus(200)
        ->assertSet('dayBlocks.2026-02-10.snack', false)
        ->assertSet('dayBlocks.2026-02-10.welcome', true);
});

it('shows day labels when the training has multiple event dates', function () {
    $teacher = User::factory()->create();
    $role = Role::query()->create(['name' => 'Teacher']);
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

    EventDate::query()->create([
        'training_id' => $training->id,
        'date' => '2026-02-11',
        'start_time' => '08:00:00',
        'end_time' => '12:00:00',
    ]);

    $teacher->roles()->attach($role->id);

    $this->actingAs($teacher)
        ->get(route('app.teacher.trainings.schedule', $training))
        ->assertSuccessful()
        ->assertSee('Dia 1')
        ->assertSee('Dia 2');
});

it('applies day border colors by schedule plan status', function () {
    $teacher = User::factory()->create();
    $role = Role::query()->create(['name' => 'Teacher']);
    $course = Course::factory()->create();
    $training = Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
    ]);

    $training->eventDates()->delete();

    EventDate::query()->create([
        'training_id' => $training->id,
        'date' => '2026-03-10',
        'start_time' => '08:00:00',
        'end_time' => '12:00:00',
    ]);
    EventDate::query()->create([
        'training_id' => $training->id,
        'date' => '2026-03-11',
        'start_time' => '08:00:00',
        'end_time' => '12:00:00',
    ]);
    EventDate::query()->create([
        'training_id' => $training->id,
        'date' => '2026-03-12',
        'start_time' => '08:00:00',
        'end_time' => '12:00:00',
    ]);

    TrainingScheduleItem::factory()->create([
        'training_id' => $training->id,
        'date' => '2026-03-10',
        'starts_at' => '2026-03-10 08:00:00',
        'ends_at' => '2026-03-10 11:00:00',
    ]);
    TrainingScheduleItem::factory()->create([
        'training_id' => $training->id,
        'date' => '2026-03-11',
        'starts_at' => '2026-03-11 08:00:00',
        'ends_at' => '2026-03-11 12:00:00',
    ]);
    TrainingScheduleItem::factory()->create([
        'training_id' => $training->id,
        'date' => '2026-03-12',
        'starts_at' => '2026-03-12 08:00:00',
        'ends_at' => '2026-03-12 12:30:00',
    ]);

    $teacher->roles()->attach($role->id);

    $this->actingAs($teacher)
        ->get(route('app.teacher.trainings.schedule', $training))
        ->assertSuccessful()
        ->assertSee('border-amber-400', false)
        ->assertSee('border-emerald-500', false)
        ->assertSee('border-red-400', false);
});

it('allows changing section duration within the 25 percent rule', function () {
    $teacher = User::factory()->create();
    $role = Role::query()->create(['name' => 'Teacher']);
    $course = Course::factory()->create();

    Section::factory()->create(['course_id' => $course->id, 'order' => 1, 'duration' => 60, 'name' => 'Parte 1']);
    Section::factory()->create(['course_id' => $course->id, 'order' => 2, 'duration' => 30, 'name' => 'Parte 2']);

    $training = Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
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

    $training->eventDates()->delete();

    EventDate::query()->create([
        'training_id' => $training->id,
        'date' => '2026-02-10',
        'start_time' => '08:00:00',
        'end_time' => '09:40:00',
    ]);

    app(\App\Services\Schedule\TrainingScheduleGenerator::class)->generate($training);

    $firstSection = TrainingScheduleItem::query()
        ->where('training_id', $training->id)
        ->where('type', 'SECTION')
        ->orderBy('starts_at')
        ->first();

    expect($firstSection)->not->toBeNull();

    $teacher->roles()->attach($role->id);

    $this->actingAs($teacher);

    $component = Livewire::test(Schedule::class, ['training' => $training]);

    $currentSection = TrainingScheduleItem::query()
        ->where('training_id', $training->id)
        ->where('type', 'SECTION')
        ->orderBy('starts_at')
        ->first();

    expect($currentSection)->not->toBeNull();

    $component
        ->set('durationInputs.'.$currentSection->id, 70)
        ->call('applyDuration', $currentSection->id)
        ->assertStatus(200);

    $updatedSection = TrainingScheduleItem::query()
        ->where('training_id', $training->id)
        ->where('section_id', $currentSection->section_id)
        ->orderBy('starts_at')
        ->first();

    expect($updatedSection)->not->toBeNull();
    expect($updatedSection?->planned_duration_minutes)->toBe(70);
});

it('updates day times and reflows schedule items', function () {
    $teacher = User::factory()->create();
    $role = Role::query()->create(['name' => 'Teacher']);
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

    $scheduleItem = TrainingScheduleItem::factory()->create([
        'training_id' => $training->id,
        'section_id' => null,
        'date' => '2026-02-10',
        'starts_at' => Carbon::parse('2026-02-10 08:00:00'),
        'ends_at' => Carbon::parse('2026-02-10 09:00:00'),
        'planned_duration_minutes' => 60,
        'suggested_duration_minutes' => null,
        'min_duration_minutes' => null,
        'position' => 1,
        'type' => 'BREAK',
        'title' => 'Intervalo',
    ]);

    $teacher->roles()->attach($role->id);
    $this->actingAs($teacher);

    Livewire::test(Schedule::class, ['training' => $training])
        ->set('dayTimes.2026-02-10.start_time', '09:00')
        ->assertStatus(200)
        ->set('dayTimes.2026-02-10.end_time', '13:30')
        ->assertStatus(200);

    $eventDate = EventDate::query()
        ->where('training_id', $training->id)
        ->where('date', '2026-02-10')
        ->first();

    expect($eventDate)->not->toBeNull();
    expect($eventDate?->start_time)->toBe('09:00:00');
    expect($eventDate?->end_time)->toBe('13:30:00');

    $updatedItem = TrainingScheduleItem::query()->find($scheduleItem->id);
    expect($updatedItem)->not->toBeNull();
    expect($updatedItem?->starts_at?->format('H:i:s'))->toBe('09:00:00');
    expect($updatedItem?->ends_at?->format('H:i:s'))->toBe('10:00:00');
});
