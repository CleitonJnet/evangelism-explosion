<?php

use App\Models\Training;
use App\Models\Course;
use Database\Seeders\TrainingsSeeder;

test('trainings seeder creates 309 trainings', function () {
    Course::query()->create(['id' => 1, 'name' => 'Curso 1']);
    Course::query()->create(['id' => 2, 'name' => 'Curso 2']);
    Course::query()->create(['id' => 3, 'name' => 'Curso 3']);

    $this->seed(TrainingsSeeder::class);

    expect(Training::query()->count())->toBe(309);
});
