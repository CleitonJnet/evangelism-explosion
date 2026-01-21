<?php

use App\Livewire\SwiperWrapperEvents;
use App\Models\Course;
use App\Models\EventDate;
use App\Models\Ministry;
use App\Models\Training;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('it mounts without a ministry filter', function () {
    $ministry = Ministry::query()->create([
        'initials' => 'EV',
        'name' => 'Evangelismo',
    ]);

    $course = Course::query()->create([
        'name' => 'Curso Base',
        'ministry_id' => $ministry->id,
    ]);

    $training = Training::query()->create([
        'course_id' => $course->id,
        'city' => 'Sao Paulo',
        'state' => 'SP',
        'price' => '0,00',
    ]);

    EventDate::query()->create([
        'training_id' => $training->id,
        'date' => '2026-02-07',
        'start_time' => '09:00:00',
    ]);

    $events = Livewire::test(SwiperWrapperEvents::class)
        ->get('events');

    expect($events)->toHaveCount(1);
    expect($events->first()->id)->toBe($training->id);
});

test('it can include or exclude events by ministry', function () {
    $ministryA = Ministry::query()->create([
        'initials' => 'EA',
        'name' => 'Evangelismo A',
    ]);

    $ministryB = Ministry::query()->create([
        'initials' => 'EB',
        'name' => 'Evangelismo B',
    ]);

    $courseA = Course::query()->create([
        'name' => 'Curso A',
        'ministry_id' => $ministryA->id,
    ]);

    $courseB = Course::query()->create([
        'name' => 'Curso B',
        'ministry_id' => $ministryB->id,
    ]);

    $trainingA = Training::query()->create([
        'course_id' => $courseA->id,
        'city' => 'Sao Paulo',
        'state' => 'SP',
        'price' => '0,00',
    ]);

    $trainingB = Training::query()->create([
        'course_id' => $courseB->id,
        'city' => 'Sao Paulo',
        'state' => 'SP',
        'price' => '0,00',
    ]);

    EventDate::query()->create([
        'training_id' => $trainingA->id,
        'date' => '2026-03-07',
        'start_time' => '09:00:00',
    ]);

    EventDate::query()->create([
        'training_id' => $trainingB->id,
        'date' => '2026-03-08',
        'start_time' => '09:00:00',
    ]);

    $included = Livewire::test(SwiperWrapperEvents::class, ['ministry' => $ministryA->id])
        ->get('events');

    expect($included)->toHaveCount(1);
    expect($included->first()->id)->toBe($trainingA->id);

    $excluded = Livewire::test(SwiperWrapperEvents::class, ['ministryNot' => [$ministryA->id]])
        ->get('events');

    expect($excluded)->toHaveCount(1);
    expect($excluded->first()->id)->toBe($trainingB->id);
});

test('it ignores trainings without event dates', function () {
    $ministry = Ministry::query()->create([
        'initials' => 'EV',
        'name' => 'Evangelismo',
    ]);

    $course = Course::query()->create([
        'name' => 'Curso Base',
        'ministry_id' => $ministry->id,
    ]);

    Training::query()->create([
        'course_id' => $course->id,
        'city' => 'Sao Paulo',
        'state' => 'SP',
        'price' => '0,00',
    ]);

    $events = Livewire::test(SwiperWrapperEvents::class)
        ->get('events');

    expect($events)->toHaveCount(0);
});

test('it orders events by their earliest event date', function () {
    $ministry = Ministry::query()->create([
        'initials' => 'EV',
        'name' => 'Evangelismo',
    ]);

    $course = Course::query()->create([
        'name' => 'Curso Base',
        'ministry_id' => $ministry->id,
    ]);

    $laterTraining = Training::query()->create([
        'course_id' => $course->id,
        'city' => 'Sao Paulo',
        'state' => 'SP',
        'price' => '0,00',
    ]);

    $earlierTraining = Training::query()->create([
        'course_id' => $course->id,
        'city' => 'Sao Paulo',
        'state' => 'SP',
        'price' => '0,00',
    ]);

    EventDate::query()->create([
        'training_id' => $laterTraining->id,
        'date' => '2026-05-10',
        'start_time' => '09:00:00',
    ]);

    EventDate::query()->create([
        'training_id' => $earlierTraining->id,
        'date' => '2026-04-10',
        'start_time' => '09:00:00',
    ]);

    $events = Livewire::test(SwiperWrapperEvents::class)
        ->get('events');

    expect($events->pluck('id')->all())->toBe([
        $earlierTraining->id,
        $laterTraining->id,
    ]);
});
