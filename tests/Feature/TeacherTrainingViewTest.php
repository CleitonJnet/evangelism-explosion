<?php

use App\Livewire\Pages\App\Teacher\Training\View;
use App\Models\Course;
use App\Models\EventDate;
use App\Models\Training;
use App\Models\TrainingScheduleItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('loads total paid students from training user pivot', function () {
    $teacher = User::factory()->create();
    $course = Course::factory()->create();
    $training = Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
        'price' => '100,00',
        'discount' => '10,00',
        'price_church' => '20,00',
    ]);

    $paidStudentOne = User::factory()->create();
    $paidStudentTwo = User::factory()->create();
    $unpaidStudent = User::factory()->create();

    $training->students()->attach($paidStudentOne->id, ['payment' => true]);
    $training->students()->attach($paidStudentTwo->id, ['payment' => 1]);
    $training->students()->attach($unpaidStudent->id, ['payment' => false]);

    Livewire::test(View::class, ['training' => $training])
        ->assertSet('paidStudentsCount', 2)
        ->assertSet('totalReceivedFromRegistrations', 'R$ 220,00')
        ->assertSet('eeMinistryBalance', 'R$ 180,00')
        ->assertSet('hostChurchExpenseBalance', 'R$ 40,00');
});

it('shows amber status when schedule has missing workload', function () {
    $teacher = User::factory()->create();
    $course = Course::factory()->create();
    $training = Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
    ]);
    $training->eventDates()->delete();

    EventDate::query()->create([
        'training_id' => $training->id,
        'date' => '2026-02-01',
        'start_time' => '08:00',
        'end_time' => '12:00',
    ]);

    TrainingScheduleItem::factory()->create([
        'training_id' => $training->id,
        'date' => '2026-02-01',
        'starts_at' => '2026-02-01 08:00:00',
        'ends_at' => '2026-02-01 11:00:00',
    ]);

    Livewire::test(View::class, ['training' => $training])
        ->assertSee('Programação:')
        ->assertSee('Carga horária incompleta')
        ->assertSee('bg-amber-100', false)
        ->assertSee('text-amber-600', false)
        ->assertSee('images/alarme.png', false);
});

it('shows red status when any schedule day exceeds the day period', function () {
    $teacher = User::factory()->create();
    $course = Course::factory()->create();
    $training = Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
    ]);
    $training->eventDates()->delete();

    EventDate::query()->create([
        'training_id' => $training->id,
        'date' => '2026-02-01',
        'start_time' => '08:00',
        'end_time' => '12:00',
    ]);

    TrainingScheduleItem::factory()->create([
        'training_id' => $training->id,
        'date' => '2026-02-01',
        'starts_at' => '2026-02-01 08:00:00',
        'ends_at' => '2026-02-01 12:30:00',
    ]);

    Livewire::test(View::class, ['training' => $training])
        ->assertSee('Programação:')
        ->assertSee('bg-red-100', false)
        ->assertSee('text-red-600', false)
        ->assertSee('images/alarme.png', false);
});

it('shows green status when schedule fully matches all day plans', function () {
    $teacher = User::factory()->create();
    $course = Course::factory()->create();
    $training = Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
    ]);
    $training->eventDates()->delete();

    $scheduleItem = TrainingScheduleItem::factory()->create([
        'training_id' => $training->id,
        'date' => '2026-02-01',
        'starts_at' => '2026-02-01 08:00:00',
        'ends_at' => '2026-02-01 12:00:00',
    ]);

    $scheduleItem = $scheduleItem->fresh();
    EventDate::query()->create([
        'training_id' => $training->id,
        'date' => $scheduleItem->date?->format('Y-m-d'),
        'start_time' => $scheduleItem->starts_at?->format('H:i'),
        'end_time' => $scheduleItem->ends_at?->format('H:i'),
    ]);

    Livewire::test(View::class, ['training' => $training])
        ->assertSee('Programação:')
        ->assertSee('bg-emerald-100', false)
        ->assertDontSee('images/alarme.png', false);
});
