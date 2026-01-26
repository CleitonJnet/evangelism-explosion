<?php

use App\Livewire\Pages\App\Teacher\Training\Create;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('lists only courses assigned to the logged in teacher', function () {
    $teacher = User::factory()->create();
    $allowedCourse = Course::factory()->create(['execution' => 0]);
    $otherCourse = Course::factory()->create(['execution' => 0]);

    $allowedCourse->teachers()->attach($teacher->id, ['status' => 1]);

    $this->actingAs($teacher);

    Livewire::test(Create::class)
        ->assertViewHas('courses', function ($courses) use ($allowedCourse, $otherCourse): bool {
            return $courses->pluck('id')->contains($allowedCourse->id)
                && ! $courses->pluck('id')->contains($otherCourse->id);
        });
});
