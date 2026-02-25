<?php

use App\Livewire\Pages\App\Teacher\Training\Index as TrainingIndex;
use App\Models\Course;
use App\Models\Training;
use App\Models\User;
use App\TrainingStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('hides past events when status is scheduled', function (): void {
    $teacher = User::factory()->create();
    $course = Course::factory()->create(['execution' => 0]);

    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'course_id' => $course->id,
        'status' => TrainingStatus::Scheduled,
    ]);

    $training->eventDates()->delete();
    $training->eventDates()->create([
        'date' => now()->subDay()->toDateString(),
        'start_time' => '09:00:00',
        'end_time' => '11:00:00',
    ]);

    $groups = Livewire::actingAs($teacher)
        ->test(TrainingIndex::class, ['statusKey' => 'scheduled'])
        ->viewData('groups');

    expect($groups->sum(fn (array $group): int => $group['items']->count()))->toBe(0);
});

it('shows past events when status is different from scheduled', function (): void {
    $teacher = User::factory()->create();
    $course = Course::factory()->create(['execution' => 0]);

    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'course_id' => $course->id,
        'status' => TrainingStatus::Canceled,
    ]);

    $training->eventDates()->delete();
    $training->eventDates()->create([
        'date' => now()->subDay()->toDateString(),
        'start_time' => '09:00:00',
        'end_time' => '11:00:00',
    ]);

    $groups = Livewire::actingAs($teacher)
        ->test(TrainingIndex::class, ['statusKey' => 'canceled'])
        ->viewData('groups');

    expect($groups->sum(fn (array $group): int => $group['items']->count()))->toBe(1);
});
