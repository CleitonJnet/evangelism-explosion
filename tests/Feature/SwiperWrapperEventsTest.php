<?php

use App\Livewire\SwiperWrapperEvents;
use App\Models\Course;
use App\Models\EventDate;
use App\Models\Training;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('shows only events with date and time after now', function () {
    Carbon::setTestNow('2026-02-11 10:00:00');

    $course = Course::factory()->create(['execution' => 0]);

    $pastTraining = Training::factory()->create(['course_id' => $course->id]);
    $pastTraining->eventDates()->delete();
    EventDate::query()->create([
        'training_id' => $pastTraining->id,
        'date' => '2026-02-11',
        'start_time' => '09:00:00',
        'end_time' => '12:00:00',
    ]);

    $futureTraining = Training::factory()->create(['course_id' => $course->id]);
    $futureTraining->eventDates()->delete();
    EventDate::query()->create([
        'training_id' => $futureTraining->id,
        'date' => '2026-02-11',
        'start_time' => '11:00:00',
        'end_time' => '13:00:00',
    ]);

    Livewire::test(SwiperWrapperEvents::class)
        ->assertSet('events', function ($events) use ($pastTraining, $futureTraining): bool {
            $trainingIds = $events->pluck('id');

            return $trainingIds->contains($futureTraining->id)
                && ! $trainingIds->contains($pastTraining->id);
        });

    Carbon::setTestNow();
});
