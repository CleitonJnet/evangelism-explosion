<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order' => (string) fake()->numberBetween(1, 20),
            'type' => fake()->randomElement(['Basico', 'Avancado', 'Especial']),
            'initials' => strtoupper(fake()->lexify('??')),
            'name' => fake()->words(3, true),
            'slogan' => fake()->sentence(),
            'learnMoreLink' => fake()->url(),
            'certificate' => fake()->randomElement(['Sim', 'Nao']),
            'color' => fake()->hexColor(),
            'price' => fake()->randomElement(['0,00', '49,90', '99,90']),
            'description' => fake()->paragraph(),
            'targetAudience' => fake()->sentence(6),
            'knowhow' => fake()->paragraph(),
            'logo' => fake()->imageUrl(),
            'banner' => fake()->imageUrl(),
            'ministry_id' => null,
        ];
    }
}
