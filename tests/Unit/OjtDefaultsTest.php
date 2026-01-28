<?php

declare(strict_types=1);

use App\Models\Course;
use App\Models\Training;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('uses course defaults when no overrides are set', function () {
    $course = Course::factory()->create([
        'execution' => 0,
        'ojt_default_count' => 6,
        'ojt_default_policy' => Training::OJT_POLICY_ROTATE,
    ]);

    $training = Training::factory()->create([
        'course_id' => $course->id,
        'ojt_count_override' => null,
        'ojt_policy_override' => null,
    ]);

    expect($training->ojtExpectedCount())->toBe(6)
        ->and($training->ojtPolicy())->toBe(Training::OJT_POLICY_ROTATE);
});

it('uses overrides when provided', function () {
    $course = Course::factory()->create([
        'execution' => 0,
        'ojt_default_count' => 6,
        'ojt_default_policy' => Training::OJT_POLICY_ROTATE,
    ]);

    $training = Training::factory()->create([
        'course_id' => $course->id,
        'ojt_count_override' => 4,
        'ojt_policy_override' => Training::OJT_POLICY_FIXED,
    ]);

    expect($training->ojtExpectedCount())->toBe(4)
        ->and($training->ojtPolicy())->toBe(Training::OJT_POLICY_FIXED);
});

it('falls back to execution when course policy is missing', function () {
    $course = Course::factory()->create([
        'execution' => 1,
        'ojt_default_count' => 8,
        'ojt_default_policy' => null,
    ]);

    $training = Training::factory()->create([
        'course_id' => $course->id,
        'ojt_policy_override' => null,
    ]);

    expect($training->ojtPolicy())->toBe(Training::OJT_POLICY_FIXED);
});
