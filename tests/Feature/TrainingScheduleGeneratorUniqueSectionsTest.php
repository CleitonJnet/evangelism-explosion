<?php

use App\Models\Course;
use App\Models\Section;
use App\Models\Training;
use App\Models\TrainingScheduleItem;
use App\Services\Schedule\TrainingScheduleGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('does not duplicate repeated course sections when generating the default schedule', function () {
    $course = Course::factory()->create();

    Section::factory()->create([
        'course_id' => $course->id,
        'order' => 1,
        'name' => 'Introducao',
        'duration' => '45',
    ]);

    Section::factory()->create([
        'course_id' => $course->id,
        'order' => 2,
        'name' => 'PRATICA 3: Saida de Treinamento Pratico (STP)',
        'duration' => '60',
    ]);

    Section::factory()->create([
        'course_id' => $course->id,
        'order' => 3,
        'name' => '  PRATICA   3:  Saida de treinamento pratico (stp) ',
        'duration' => '60',
    ]);

    $training = Training::factory()->create([
        'course_id' => $course->id,
    ]);

    $training->eventDates()->delete();
    $training->eventDates()->create([
        'date' => now()->addWeek()->toDateString(),
        'start_time' => '08:00:00',
        'end_time' => '18:00:00',
    ]);

    app(TrainingScheduleGenerator::class)->generate($training->fresh());

    $sectionTitles = TrainingScheduleItem::query()
        ->where('training_id', $training->id)
        ->where('type', 'SECTION')
        ->pluck('title');

    $normalizedTitleCounts = $sectionTitles
        ->map(fn (?string $title): string => Str::of((string) $title)->squish()->lower()->toString())
        ->countBy()
        ->filter(fn (int $count): bool => $count > 1);

    expect($normalizedTitleCounts)->toBeEmpty();
    expect($sectionTitles)->toHaveCount(2);
});
