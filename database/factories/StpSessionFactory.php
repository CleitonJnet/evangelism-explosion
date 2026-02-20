<?php

namespace Database\Factories;

use App\Models\Training;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StpSession>
 */
class StpSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'training_id' => Training::factory(),
            'sequence' => fake()->numberBetween(1, 10),
            'label' => fake()->optional()->sentence(2),
            'starts_at' => fake()->optional()->dateTimeBetween('now', '+30 days'),
            'ends_at' => fake()->optional()->dateTimeBetween('+1 hour', '+31 days'),
            'status' => fake()->optional()->randomElement(['planned', 'in_progress', 'done']),
        ];
    }
}
