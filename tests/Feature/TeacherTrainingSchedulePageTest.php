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

    TrainingScheduleItem::factory()->create([
        'training_id' => $training->id,
        'date' => $eventDate?->date,
        'starts_at' => Carbon::parse($eventDate?->date.' 09:00:00'),
        'ends_at' => Carbon::parse($eventDate?->date.' 10:00:00'),
    ]);

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
        ->assertSee('Arrastar para reordenar');
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

it('keeps a single welcome session and inserts devotionals', function () {
    $teacher = User::factory()->create();
    $course = Course::factory()->create();
    $training = Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
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
        'start_time' => '09:00:00',
        'end_time' => '12:00:00',
    ]);

    TrainingScheduleItem::query()->create([
        'training_id' => $training->id,
        'section_id' => null,
        'date' => '2026-02-10',
        'starts_at' => Carbon::parse('2026-02-10 08:00:00'),
        'ends_at' => Carbon::parse('2026-02-10 08:30:00'),
        'type' => 'WELCOME',
        'title' => 'Boas-vindas',
        'planned_duration_minutes' => 30,
        'suggested_duration_minutes' => null,
        'min_duration_minutes' => null,
        'origin' => 'TEACHER',
        'is_locked' => false,
        'status' => 'OK',
        'conflict_reason' => null,
        'meta' => null,
    ]);

    TrainingScheduleItem::query()->create([
        'training_id' => $training->id,
        'section_id' => null,
        'date' => '2026-02-10',
        'starts_at' => Carbon::parse('2026-02-10 08:30:00'),
        'ends_at' => Carbon::parse('2026-02-10 09:00:00'),
        'type' => 'WELCOME',
        'title' => 'Boas-vindas extra',
        'planned_duration_minutes' => 30,
        'suggested_duration_minutes' => null,
        'min_duration_minutes' => null,
        'origin' => 'TEACHER',
        'is_locked' => false,
        'status' => 'OK',
        'conflict_reason' => null,
        'meta' => null,
    ]);

    TrainingScheduleItem::query()->create([
        'training_id' => $training->id,
        'section_id' => null,
        'date' => '2026-02-10',
        'starts_at' => Carbon::parse('2026-02-10 09:00:00'),
        'ends_at' => Carbon::parse('2026-02-10 09:30:00'),
        'type' => 'DEVOTIONAL',
        'title' => 'Devocional duplicado',
        'planned_duration_minutes' => 30,
        'suggested_duration_minutes' => null,
        'min_duration_minutes' => null,
        'origin' => 'AUTO',
        'is_locked' => false,
        'status' => 'OK',
        'conflict_reason' => null,
        'meta' => ['anchor' => 'devotional_after_welcome'],
    ]);

    TrainingScheduleItem::query()->create([
        'training_id' => $training->id,
        'section_id' => null,
        'date' => '2026-02-10',
        'starts_at' => Carbon::parse('2026-02-10 09:30:00'),
        'ends_at' => Carbon::parse('2026-02-10 10:00:00'),
        'type' => 'DEVOTIONAL',
        'title' => 'Devocional duplicado 2',
        'planned_duration_minutes' => 30,
        'suggested_duration_minutes' => null,
        'min_duration_minutes' => null,
        'origin' => 'AUTO',
        'is_locked' => false,
        'status' => 'OK',
        'conflict_reason' => null,
        'meta' => ['anchor' => 'devotional_after_welcome'],
    ]);

    $this->actingAs($teacher);

    Livewire::test(Schedule::class, ['training' => $training])
        ->assertStatus(200)
        ->assertSet('scheduleSettings.days.2026-02-10.meals.afternoon_snack.enabled', false)
        ->assertSet('scheduleSettings.days.2026-02-10.meals.dinner.enabled', false);

    $welcomeCount = TrainingScheduleItem::query()
        ->where('training_id', $training->id)
        ->where('type', 'WELCOME')
        ->count();

    expect($welcomeCount)->toBe(1);

    $devotionalCount = TrainingScheduleItem::query()
        ->where('training_id', $training->id)
        ->where('type', 'DEVOTIONAL')
        ->count();

    expect($devotionalCount)->toBe(2);
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

it('allows changing section duration within the 20 percent rule', function () {
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

    app(\App\Services\Schedule\TrainingScheduleGenerator::class)->generate($training, 'FULL');

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
        ->call(
            'updateDuration',
            $currentSection->id,
            $currentSection->date->format('Y-m-d'),
            $currentSection->starts_at->format('Y-m-d H:i:s'),
            70
        )
        ->assertStatus(200);

    $updatedSection = TrainingScheduleItem::query()
        ->where('training_id', $training->id)
        ->where('section_id', $currentSection->section_id)
        ->orderBy('starts_at')
        ->first();

    expect($updatedSection)->not->toBeNull();
    expect($updatedSection?->planned_duration_minutes)->toBe(70);
});
