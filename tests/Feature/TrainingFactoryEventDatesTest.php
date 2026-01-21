<?php

use App\Models\Church;
use App\Models\Training;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

test('training factory creates three event dates for course 1', function () {
    DB::table('courses')->insert([
        'id' => 1,
        'name' => 'Curso 1',
        'price' => '0,00',
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
    ]);
    Church::factory()->create();
    $teacher = User::factory()->create();

    $training = Training::factory()
        ->courseOne()
        ->state(fn () => ['teacher_id' => $teacher->id])
        ->create();

    expect($training->eventDates)->toHaveCount(3);
});

test('training factory creates two event dates for course 8', function () {
    DB::table('courses')->insert([
        'id' => 8,
        'name' => 'Curso 8',
        'price' => '0,00',
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
    ]);
    Church::factory()->create();
    $teacher = User::factory()->create();

    $training = Training::factory()
        ->state(fn () => ['course_id' => 8, 'teacher_id' => $teacher->id])
        ->create();

    expect($training->eventDates)->toHaveCount(2);
});

test('training factory creates one event date for other courses', function () {
    DB::table('courses')->insert([
        'id' => 2,
        'name' => 'Curso 2',
        'price' => '0,00',
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
    ]);
    Church::factory()->create();
    $teacher = User::factory()->create();

    $training = Training::factory()
        ->courseTwo()
        ->state(fn () => ['teacher_id' => $teacher->id])
        ->create();

    expect($training->eventDates)->toHaveCount(1);
});
