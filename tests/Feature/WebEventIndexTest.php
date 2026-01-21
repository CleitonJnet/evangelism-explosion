<?php

use App\Livewire\Web\Event\Index;
use App\Models\Church;
use App\Models\Course;
use App\Models\EventDate;
use App\Models\Ministry;
use App\Models\Training;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

test('web events index uses event dates from the database', function () {
    Carbon::setTestNow('2026-02-01 00:00:00');
    Cache::flush();

    $ministry = Ministry::create([
        'name' => 'Everyday Evangelism',
        'initials' => 'EV2',
        'color' => '#1F2937',
    ]);

    $course = Course::create([
        'name' => 'Evangelismo Eficaz',
        'type' => 'Clinica',
        'color' => '#0F172A',
        'ministry_id' => $ministry->id,
    ]);

    $church = Church::create([
        'name' => 'Igreja Central',
        'city' => 'Niteroi',
        'state' => 'RJ',
    ]);

    $training = Training::create([
        'course_id' => $course->id,
        'church_id' => $church->id,
        'city' => 'Niteroi',
        'state' => 'RJ',
    ]);

    EventDate::create([
        'training_id' => $training->id,
        'date' => '2026-02-06',
        'start_time' => '08:00:00',
        'end_time' => '12:00:00',
        'status' => 1,
    ]);

    $todayTraining = Training::create([
        'course_id' => $course->id,
        'church_id' => $church->id,
        'city' => 'Niteroi',
        'state' => 'RJ',
    ]);

    EventDate::create([
        'training_id' => $todayTraining->id,
        'date' => '2026-02-01',
        'start_time' => '09:00:00',
        'end_time' => '11:00:00',
        'status' => 1,
    ]);

    $canceledTraining = Training::create([
        'course_id' => $course->id,
        'church_id' => $church->id,
        'city' => 'Niteroi',
        'state' => 'RJ',
    ]);

    EventDate::create([
        'training_id' => $canceledTraining->id,
        'date' => '2026-02-10',
        'start_time' => '08:00:00',
        'end_time' => '12:00:00',
        'status' => 2,
    ]);

    Livewire::test(Index::class)
        ->assertCount('events', 2)
        ->assertSet('events.0.id', (string) $todayTraining->id)
        ->assertSet('events.0.date', '2026-02-01')
        ->assertSet('events.1.id', (string) $training->id)
        ->assertSet('events.1.date', '2026-02-06')
        ->assertSet('events.1.hour_s', '08:00')
        ->assertSee('Evangelismo Eficaz')
        ->assertSee('Niteroi');

    Carbon::setTestNow();
});
