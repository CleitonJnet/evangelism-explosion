<?php

namespace Database\Factories;

use App\Models\Church;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $contactName = $this->personName();

        return [
            'pastor' => fake()->optional(0.2)->randomElement([
                'Pr. '.$this->personName(),
                'Pra. '.$this->personName(),
            ]),
            'name' => $contactName,
            'birthdate' => fake()->optional(0.8)->date('Y-m-d'),
            'gender' => fake()->optional(0.9)->randomElement(['Masculino', 'Feminino', 'Outro']),
            'profile_photo_path' => null,
            'phone' => $this->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'street' => $this->streetName(),
            'number' => (string) fake()->numberBetween(1, 9999),
            'complement' => fake()->optional(0.3)->randomElement([
                'Apto 101',
                'Casa 2',
                'Bloco B',
                'Fundos',
            ]),
            'district' => $this->districtName(),
            'city' => $this->cityName(),
            'state' => $this->stateCode(),
            'postal_code' => $this->postalCode(),
            'notes' => fake()->optional(0.2)->sentence(12),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('Master@01'),
            'remember_token' => Str::random(10),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'church_id' => $this->randomChurchId(),
            'church_temp_id' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }

    private function phoneNumber(): string
    {
        $ddd = fake()->randomElement(['11', '21', '31', '41', '51', '61', '71', '81', '85', '91']);
        $suffix = str_pad((string) fake()->numberBetween(0, 99999999), 8, '0', STR_PAD_LEFT);

        return $ddd.'9'.$suffix;
    }

    private function postalCode(): string
    {
        return str_pad((string) fake()->numberBetween(0, 99999999), 8, '0', STR_PAD_LEFT);
    }

    private function streetName(): string
    {
        return sprintf('%s %s', fake()->randomElement([
            'Rua',
            'Avenida',
            'Travessa',
            'Alameda',
        ]), fake()->randomElement([
            'das Flores',
            'dos Ipes',
            'Brasil',
            'Independencia',
            'Santos Dumont',
            'Paulista',
            'Getulio Vargas',
            'Dom Pedro II',
            'Rio Branco',
            'Sete de Setembro',
        ]));
    }

    private function districtName(): string
    {
        return fake()->randomElement([
            'Centro',
            'Jardim America',
            'Vila Nova',
            'Boa Vista',
            'Santa Clara',
            'Vila Esperanca',
            'Bela Vista',
            'Jardim das Oliveiras',
            'Nova Esperanca',
            'Parque das Nacoes',
        ]);
    }

    private function cityName(): string
    {
        return fake()->randomElement([
            'Sao Paulo',
            'Rio de Janeiro',
            'Belo Horizonte',
            'Curitiba',
            'Porto Alegre',
            'Salvador',
            'Recife',
            'Fortaleza',
            'Goiania',
            'Brasilia',
            'Belem',
            'Manaus',
        ]);
    }

    private function stateCode(): string
    {
        return fake()->randomElement(['SP', 'RJ', 'MG', 'PR', 'RS', 'BA', 'PE', 'CE', 'GO', 'DF', 'PA', 'AM']);
    }

    private function personName(): string
    {
        $firstName = fake()->randomElement([
            'Andre',
            'Bruno',
            'Carlos',
            'Daniel',
            'Eduardo',
            'Felipe',
            'Gabriel',
            'Henrique',
            'Joao',
            'Lucas',
            'Marcos',
            'Paulo',
            'Rafael',
            'Tiago',
            'Vitor',
            'Amanda',
            'Beatriz',
            'Camila',
            'Carolina',
            'Fernanda',
            'Isabela',
            'Juliana',
            'Larissa',
            'Mariana',
            'Patricia',
            'Renata',
        ]);
        $lastName = fake()->randomElement([
            'Almeida',
            'Barbosa',
            'Cardoso',
            'Carvalho',
            'Costa',
            'Dias',
            'Ferreira',
            'Gomes',
            'Lima',
            'Martins',
            'Moura',
            'Oliveira',
            'Pereira',
            'Ramos',
            'Rezende',
            'Ribeiro',
            'Rocha',
            'Santana',
            'Silva',
            'Souza',
        ]);

        return sprintf('%s %s', $firstName, $lastName);
    }

    private function randomChurchId(): ?int
    {
        return Church::query()->inRandomOrder()->value('id');
    }
}
