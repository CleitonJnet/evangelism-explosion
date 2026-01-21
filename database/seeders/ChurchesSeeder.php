<?php

namespace Database\Seeders;

use App\Models\Church;
use Illuminate\Database\Seeder;

class ChurchesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Church::create([ 'id'=>1, 'name' => 'Primeira Igreja Batista do Ingá', 'pastor' => 'Pr. Edimar Guimarães Pereira', 'email' => 'secretaria@pibinga.org.br', 'phone' => '21984945914', 'street' => 'Rua Dr. Paulo Alves', 'number' => '125', 'complement' => null, 'district' => 'Ingá', 'city' => 'Niterói', 'state' => 'RJ', 'postal_code' => '24210445', 'contact' => 'Monica Moura', 'contact_phone' => '21922222222', 'contact_email' => 'teste@teste', 'notes' => null, 'logo' => null, ]);
        Church::create([ 'id'=>2, 'name' => 'Primeira Igreja Batista de Itaipú', 'pastor' => 'Pr. Tristão', 'email' => 'secretaria@pibitaipu.org.br', 'phone' => '21984945914', 'street' => 'Rua Marquês de Paraná', 'number' => '125', 'complement' => null, 'district' => 'Itaipu', 'city' => 'Niterói', 'state' => 'RJ', 'postal_code' => '24210445', 'contact' => 'Monica Moura', 'contact_phone' => '21922222222', 'contact_email' => 'teste@teste', 'notes' => null, 'logo' => null, ]);
        Church::create([ 'id'=>3, 'name' => 'Igreja Batista Memorial em Rondonópolis', 'pastor' => 'Pr. Cleverson Rodrigues', 'email' => 'secretaria@ibmr.org.br', 'phone' => '21984945914', 'street' => 'Rua Marquês de Paraná', 'number' => '125', 'complement' => null, 'district' => 'Centro', 'city' => 'Rondonópolis', 'state' => 'MT', 'postal_code' => '24210445', 'contact' => 'Monica Moura', 'contact_phone' => '21922222222', 'contact_email' => 'teste@teste', 'notes' => null, 'logo' => null, ]);

        Church::factory()
            ->count(1000)
            ->create();
    }
}
