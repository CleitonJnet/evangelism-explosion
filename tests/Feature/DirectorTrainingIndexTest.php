<?php

use App\Livewire\Pages\App\Director\Training\Index;
use App\Models\Course;
use App\Models\Training;
use App\TrainingStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('filters trainings by status key and course execution', function () {
    $course = Course::factory()->create(['execution' => 0]);

    $planning = Training::factory()->create([
        'course_id' => $course->id,
        'status' => TrainingStatus::Planning->value,
    ]);

    Training::factory()->create([
        'course_id' => $course->id,
        'status' => TrainingStatus::Scheduled->value,
    ]);

    Livewire::test(Index::class, ['statusKey' => 'planning'])
        ->assertViewHas('groups', function ($groups) use ($planning): bool {
            $items = $groups->first()['items'] ?? collect();

            return $items->count() === 1
                && $items->first()['training']->id === $planning->id;
        });
});

it('includes trainings from extra course ids', function () {
    $extraCourse = Course::factory()->create(['execution' => 1]);
    $planning = Training::factory()->create([
        'course_id' => $extraCourse->id,
        'status' => TrainingStatus::Planning->value,
    ]);

    Livewire::test(Index::class, ['statusKey' => 'planning'])
        ->set('extraCourseIds', [$extraCourse->id])
        ->assertViewHas('groups', function ($groups) use ($planning): bool {
            $items = $groups->first()['items'] ?? collect();

            return $items->contains(fn ($item) => $item['training']->id === $planning->id);
        });
});
