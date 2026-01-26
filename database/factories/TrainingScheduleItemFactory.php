<?php

namespace Database\Factories;

use App\Models\Section;
use App\Models\Training;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TrainingScheduleItem>
 */
class TrainingScheduleItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = Carbon::today();
        $start = $date->copy()->setTime(9, 0);
        $duration = fake()->numberBetween(30, 90);

        return [
            'training_id' => Training::factory(),
            'section_id' => Section::factory(),
            'date' => $date->format('Y-m-d'),
            'starts_at' => $start->copy(),
            'ends_at' => $start->copy()->addMinutes($duration),
            'type' => 'SECTION',
            'title' => fake()->sentence(3),
            'planned_duration_minutes' => $duration,
            'suggested_duration_minutes' => $duration,
            'min_duration_minutes' => (int) ceil($duration * 0.75),
            'origin' => 'AUTO',
            'is_locked' => false,
            'status' => 'OK',
            'conflict_reason' => null,
            'meta' => null,
        ];
    }
}
