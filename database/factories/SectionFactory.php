<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Section>
 */
class SectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'banner' => fake()->optional()->imageUrl(),
            'order' => fake()->numberBetween(1, 20),
            'duration' => fake()->randomElement(['30 min', '45 min', '1h', '1h30']),
            'devotional' => fake()->optional()->sentence(6),
            'description' => fake()->optional()->paragraph(),
            'knowhow' => fake()->optional()->paragraph(),
            'course_id' => Course::factory(),
        ];
    }
}
