<?php

namespace Database\Factories;

use App\TrainingStatus;
use App\Models\Church;
use App\Models\Course;
use App\Models\EventDate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Training>
 */
class TrainingFactory extends Factory
{
    /**
     * @var array<int, int>
     */
    protected static array $courseIds = [];

    /**
     * @var array<int, int>
     */
    protected static array $teacherIds = [];

    /**
     * @var array<int, int>
     */
    protected static array $churchIds = [];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $location = $this->location();

        return [
            'banner' => null,
            'coordinator' => sprintf('Pr. %s', $this->personName()),
            'phone' => $this->phoneNumber(),
            'email' => $this->trainingEmail($location['city']),
            'street' => $this->streetName(),
            'number' => (string) fake()->numberBetween(1, 9999),
            'complement' => fake()->optional(0.3)->randomElement([
                'Sala 101',
                'Anexo A',
                'Casa 2',
                'Fundos',
                'Bloco B',
            ]),
            'district' => $this->districtName(),
            'city' => $location['city'],
            'state' => $location['state'],
            'postal_code' => $this->postalCode(),
            'url' => $this->trainingUrl($location['city']),
            'price' => $this->moneyValue(),
            'price_church' => $this->moneyValue(),
            'discount' => fake()->optional(0.4)->randomElement(['5,00', '10,00', '15,00', '20,00']),
            'kits' => fake()->numberBetween(0, 300),
            'totStudents' => fake()->numberBetween(0, 300),
            'totChurches' => fake()->numberBetween(0, 120),
            'totNewChurches' => fake()->numberBetween(0, 60),
            'totPastors' => fake()->numberBetween(0, 60),
            'totKitsUsed' => fake()->numberBetween(0, 250),
            'totListeners' => fake()->numberBetween(0, 300),
            'totKitsReceived' => fake()->numberBetween(0, 300),
            'totApproaches' => fake()->numberBetween(0, 400),
            'totDecisions' => fake()->numberBetween(0, 200),
            'notes' => fake()->optional(0.2)->sentence(12),
            'status' => fake()->randomElement(TrainingStatus::cases()),
            'course_id' => $this->randomCourseId(),
            'teacher_id' => $this->randomTeacherId(),
            'church_id' => $this->randomChurchId(),
        ];
    }

    public function courseOne(): static
    {
        return $this->state(fn (array $attributes) => [
            'course_id' => 1,
        ]);
    }

    public function courseTwo(): static
    {
        return $this->state(fn (array $attributes) => [
            'course_id' => 2,
        ]);
    }

    public function courseThree(): static
    {
        return $this->state(fn (array $attributes) => [
            'course_id' => 3,
        ]);
    }

    public function configure(): static
    {
        return $this->afterCreating(function ($training): void {
            $schedule = $this->scheduleForCourse($training->course_id);
            $baseDate = Carbon::today()->addDays(fake()->numberBetween(5, 120));

            foreach ($schedule as $index => $slot) {
                EventDate::query()->create([
                    'training_id' => $training->id,
                    'date' => $baseDate->copy()->addDays($index)->format('Y-m-d'),
                    'start_time' => $slot['start_time'],
                    'end_time' => $slot['end_time'],
                ]);
            }
        });
    }

    /**
     * @return array<int, array{start_time: string, end_time: string}>
     */
    private function scheduleForCourse(?int $courseId): array
    {
        if ($courseId === 1) {
            return [
                ['start_time' => '19:00:00', 'end_time' => '21:30:00'],
                ['start_time' => '08:00:00', 'end_time' => '22:00:00'],
                ['start_time' => '08:00:00', 'end_time' => '18:00:00'],
            ];
        }

        if ($courseId === 8) {
            return [
                ['start_time' => '08:00:00', 'end_time' => '17:30:00'],
                ['start_time' => '08:00:00', 'end_time' => '17:30:00'],
            ];
        }

        return [
            ['start_time' => '09:00:00', 'end_time' => '17:00:00'],
        ];
    }

    /**
     * @return array{city: string, state: string}
     */
    private function location(): array
    {
        return fake()->randomElement([
            ['city' => 'Sao Paulo', 'state' => 'SP'],
            ['city' => 'Rio de Janeiro', 'state' => 'RJ'],
            ['city' => 'Belo Horizonte', 'state' => 'MG'],
            ['city' => 'Curitiba', 'state' => 'PR'],
            ['city' => 'Porto Alegre', 'state' => 'RS'],
            ['city' => 'Salvador', 'state' => 'BA'],
            ['city' => 'Recife', 'state' => 'PE'],
            ['city' => 'Fortaleza', 'state' => 'CE'],
            ['city' => 'Goiania', 'state' => 'GO'],
            ['city' => 'Brasilia', 'state' => 'DF'],
            ['city' => 'Belem', 'state' => 'PA'],
            ['city' => 'Manaus', 'state' => 'AM'],
        ]);
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

    private function phoneNumber(): string
    {
        $ddd = fake()->randomElement(['11', '21', '31', '41', '51', '61', '71', '81', '85', '91']);
        $suffix = str_pad((string) fake()->numberBetween(0, 99999999), 8, '0', STR_PAD_LEFT);

        return $ddd . '9' . $suffix;
    }

    private function postalCode(): string
    {
        return str_pad((string) fake()->numberBetween(0, 99999999), 8, '0', STR_PAD_LEFT);
    }

    private function trainingEmail(string $city): string
    {
        $localPart = Str::slug('treinamento-' . $city, '.');
        $domain = fake()->randomElement(['igreja.org.br', 'ministerio.com.br', 'comunidade.com.br']);

        return $localPart . '@' . $domain;
    }

    private function trainingUrl(string $city): string
    {
        $slug = Str::slug('treinamento-' . $city);

        return 'https://www.' . $slug . '.org.br';
    }

    private function moneyValue(): string
    {
        return fake()->randomElement(['0,00', '50,00', '100,00', '150,00', '200,00']);
    }

    private function randomCourseId(): ?int
    {
        if (self::$courseIds === []) {
            self::$courseIds = Course::query()->pluck('id')->all();
        }

        if (self::$courseIds === []) {
            return null;
        }

        return fake()->randomElement(self::$courseIds);
    }

    private function randomTeacherId(): ?int
    {
        if (self::$teacherIds === []) {
            self::$teacherIds = User::query()->pluck('id')->all();
        }

        if (self::$teacherIds === []) {
            return null;
        }

        return fake()->randomElement(self::$teacherIds);
    }

    private function randomChurchId(): ?int
    {
        if (self::$churchIds === []) {
            self::$churchIds = Church::query()->pluck('id')->all();
        }

        if (self::$churchIds === []) {
            return null;
        }

        return fake()->randomElement(self::$churchIds);
    }
}
