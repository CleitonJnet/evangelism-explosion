<?php

use App\Models\Course;
use App\Models\EventDate;
use App\Models\Role;
use App\Models\Training;
use App\Models\TrainingScheduleItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows schedule error alert on toolbar when schedule does not match', function () {
    $teacher = User::factory()->create();
    $role = Role::query()->create(['name' => 'Teacher']);
    $course = Course::factory()->create();
    $training = Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
    ]);

    $teacher->roles()->attach($role->id);

    $this->actingAs($teacher)
        ->get(route('app.teacher.trainings.show', $training))
        ->assertSuccessful()
        ->assertSee('images/alarme.png', false);
});

it('does not show schedule error alert on toolbar when all days match', function () {
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
        'date' => '2026-02-01',
        'start_time' => '08:00:00',
        'end_time' => '12:00:00',
    ]);

    TrainingScheduleItem::factory()->create([
        'training_id' => $training->id,
        'date' => '2026-02-01',
        'starts_at' => '2026-02-01 08:00:00',
        'ends_at' => '2026-02-01 12:00:00',
    ]);

    $teacher->roles()->attach($role->id);

    $this->actingAs($teacher)
        ->get(route('app.teacher.trainings.show', $training))
        ->assertSuccessful()
        ->assertDontSee('images/alarme.png', false);
});
