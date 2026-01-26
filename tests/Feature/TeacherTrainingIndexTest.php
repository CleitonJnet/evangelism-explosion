<?php

use App\Livewire\Pages\App\Teacher\Training\Index;
use App\Models\Course;
use App\Models\Training;
use App\Models\User;
use App\TrainingStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('lists only trainings for the logged in teacher', function () {
    $teacher = User::factory()->create();
    $otherTeacher = User::factory()->create();
    $course = Course::factory()->create(['execution' => 0]);

    $ownedTraining = Training::factory()->create([
        'course_id' => $course->id,
        'status' => TrainingStatus::Planning->value,
        'teacher_id' => $teacher->id,
    ]);

    $otherTraining = Training::factory()->create([
        'course_id' => $course->id,
        'status' => TrainingStatus::Planning->value,
        'teacher_id' => $otherTeacher->id,
    ]);

    $this->actingAs($teacher);

    Livewire::test(Index::class, ['statusKey' => 'planning'])
        ->assertViewHas('groups', function ($groups) use ($ownedTraining, $otherTraining): bool {
            $trainingIds = $groups
                ->flatMap(fn ($group) => $group['items']->map(fn ($item) => $item['training']->id))
                ->values();

            return $trainingIds->contains($ownedTraining->id)
                && ! $trainingIds->contains($otherTraining->id);
        });
});

it('orders trainings by course type and name', function () {
    $teacher = User::factory()->create();
    $firstCourse = Course::factory()->create([
        'execution' => 0,
        'type' => 'A',
        'name' => 'Alpha',
    ]);
    $secondCourse = Course::factory()->create([
        'execution' => 0,
        'type' => 'B',
        'name' => 'Beta',
    ]);

    Training::factory()->create([
        'course_id' => $secondCourse->id,
        'status' => TrainingStatus::Planning->value,
        'teacher_id' => $teacher->id,
    ]);

    Training::factory()->create([
        'course_id' => $firstCourse->id,
        'status' => TrainingStatus::Planning->value,
        'teacher_id' => $teacher->id,
    ]);

    $this->actingAs($teacher);

    Livewire::test(Index::class, ['statusKey' => 'planning'])
        ->assertViewHas('groups', function ($groups) use ($firstCourse, $secondCourse): bool {
            $firstGroupCourse = $groups->first()['course'] ?? null;
            $secondGroupCourse = $groups->get(1)['course'] ?? null;

            return $firstGroupCourse?->id === $firstCourse->id
                && $secondGroupCourse?->id === $secondCourse->id;
        });
});
