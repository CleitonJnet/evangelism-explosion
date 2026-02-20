<?php

namespace Database\Factories;

use App\Enums\StpApproachStatus;
use App\Enums\StpApproachType;
use App\Models\StpSession;
use App\Models\Training;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StpApproach>
 */
class StpApproachFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $training = Training::factory();
        $session = StpSession::factory()->state([
            'training_id' => $training,
        ]);

        return [
            'training_id' => $training,
            'stp_session_id' => $session,
            'stp_team_id' => null,
            'type' => fake()->randomElement([
                StpApproachType::Visitor->value,
                StpApproachType::SecurityQuestionnaire->value,
                StpApproachType::Indication->value,
                StpApproachType::Lifestyle->value,
            ]),
            'status' => StpApproachStatus::Planned->value,
            'position' => fake()->numberBetween(0, 20),
            'person_name' => fake()->name(),
            'phone' => fake()->optional()->phoneNumber(),
            'email' => fake()->optional()->safeEmail(),
            'street' => fake()->optional()->streetName(),
            'number' => fake()->optional()->buildingNumber(),
            'complement' => fake()->optional()->secondaryAddress(),
            'district' => fake()->optional()->citySuffix(),
            'city' => fake()->optional()->city(),
            'state' => fake()->optional()->stateAbbr(),
            'postal_code' => fake()->optional()->postcode(),
            'reference_point' => fake()->optional()->sentence(3),
            'gospel_explained_times' => fake()->optional()->numberBetween(0, 5),
            'people_count' => fake()->optional()->numberBetween(1, 10),
            'result' => null,
            'means_growth' => false,
            'follow_up_scheduled_at' => null,
            'public_q2_answer' => null,
            'public_lesson' => null,
            'created_by_user_id' => User::factory(),
            'reported_by_user_id' => null,
            'reviewed_by_user_id' => null,
            'reviewed_at' => null,
            'payload' => null,
        ];
    }
}
