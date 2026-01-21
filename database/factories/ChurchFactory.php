<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Church>
 */
class ChurchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $location = $this->location();
        $descriptor = $this->churchDescriptor();
        $denomination = $this->denomination();
        $contactName = $this->personName();

        return [
            'name' => sprintf('%s %s de %s', $denomination, $descriptor, $location['city']),
            'pastor' => sprintf('Pr. %s', $this->personName()),
            'email' => $this->churchEmail($descriptor, $location['city']),
            'phone' => $this->phoneNumber(),
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
            'contact' => $contactName,
            'contact_phone' => $this->phoneNumber(),
            'contact_email' => $this->contactEmail($contactName),
            'notes' => fake()->optional(0.2)->sentence(12),
            'logo' => null,
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

    private function denomination(): string
    {
        return fake()->randomElement([
            'Igreja Batista',
            'Igreja Presbiteriana',
            'Igreja Assembleia de Deus',
            'Igreja Evangelica',
            'Igreja do Nazareno',
            'Igreja Comunidade Crista',
        ]);
    }

    private function churchDescriptor(): string
    {
        return fake()->randomElement([
            'Central',
            'Memorial',
            'Vida Nova',
            'Nova Alianca',
            'Betel',
            'Ebenezer',
            'Esperanca',
            'Monte Sinai',
            'Jardim',
            'Vila Nova',
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
            'dos Ipês',
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

    private function churchEmail(string $descriptor, string $city): string
    {
        $localPart = Str::slug($descriptor . '-' . $city, '.');
        $domain = fake()->randomElement(['igreja.org.br', 'ministerio.com.br', 'comunidade.com.br']);

        return $localPart . '@' . $domain;
    }

    private function contactEmail(string $contactName): string
    {
        $localPart = Str::slug($contactName, '.');
        $domain = fake()->randomElement(['gmail.com', 'hotmail.com', 'outlook.com', 'uol.com.br']);

        return $localPart . '@' . $domain;
    }
}
