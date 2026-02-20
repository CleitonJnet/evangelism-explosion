<?php

namespace Database\Factories;

use App\Models\StpSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StpTeam>
 */
class StpTeamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'stp_session_id' => StpSession::factory(),
            'mentor_user_id' => User::factory(),
            'name' => sprintf('Equipe %02d', fake()->numberBetween(1, 99)),
            'position' => fake()->numberBetween(0, 10),
        ];
    }
}
