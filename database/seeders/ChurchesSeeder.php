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
        // Church::create([ 'id'=>1, 'name' => 'Primeira Igreja Batista do Ingá', 'pastor' => 'Pr. Edimar Guimarães Pereira', 'email' => 'secretaria@pibinga.org.br', 'phone' => '21984945914', 'street' => 'Rua Dr. Paulo Alves', 'number' => '125', 'complement' => null, 'district' => 'Ingá', 'city' => 'Niterói', 'state' => 'RJ', 'postal_code' => '24210445', 'contact' => 'Monica Moura', 'contact_phone' => '21922222222', 'contact_email' => 'teste@teste', 'notes' => null, 'logo' => null, ]);

        Church::updateOrCreate(
            ['name' => 'Primeira Igreja Batista do Ingá', 'city' => 'Niterói', 'state' => 'RJ'],
            [
                'pastor' => 'Pr. Edimar Guimarães Pereira',
                'email' => 'secretaria@pibinga.org.br',
                'phone' => '2126211268',
                'street' => 'Rua Doutor Paulo Alves',
                'number' => '125',
                'complement' => null,
                'district' => 'Ingá',
                'city' => 'Niterói',
                'state' => 'RJ',
                'postal_code' => '24210445',
                'contact' => 'Secretaria',
                'contact_phone' => '21984945914',
                'contact_email' => 'secretaria@pibinga.org.br',
                'notes' => 'Site: pibinga.org.br | Tel.: (21) 2621-1268 | WhatsApp: (21) 98494-5914 | End.: Rua Doutor Paulo Alves, 125 - Ingá - Niterói/RJ - CEP 24210-445 | Email do pastor (Atos6): ediguimaraes@yahoo.com.br',
                'logo' => 'https://ik.imagekit.io/a6/uploads/organization/avatar/3744/4a5753cf-8004-42e6-8519-3af3d27734ff.jpg?tr=h-100%2Cw-100',
            ]
        );

        Church::updateOrCreate(
            ['name' => 'Primeira Igreja Batista de Niterói', 'city' => 'Niterói', 'state' => 'RJ'],
            [
                'pastor' => 'Pr. Paschoal Piragine Júnior',
                'email' => 'secretaria@pibn.org.br',
                'phone' => '2127220355',
                'street' => 'Av. Marquês do Paraná',
                'number' => '225',
                'complement' => null,
                'district' => 'Centro',
                'city' => 'Niterói',
                'state' => 'RJ',
                'postal_code' => '24030215',
                'contact' => 'Secretaria',
                'contact_phone' => '21984976149',
                'contact_email' => 'secretaria@pibn.org.br',
                'notes' => 'Site: pibn.org.br | End.: Av. Marquês do Paraná, 225 - Centro - Niterói/RJ - CEP 24030-215 | Tel.: (21) 2722-0355 | WhatsApp: (21) 98497-6149 | Cultos (perfil público): Dom 10h e 19h | Qua 19h30',
                'logo' => null,
            ]
        );

        Church::updateOrCreate(
            ['name' => 'Primeira Igreja Batista em Heliópolis', 'city' => 'Belford Roxo', 'state' => 'RJ'],
            [
                'pastor' => 'Pr. Davidson Freitas',
                'email' => 'equipecomunicacao.pibh@gmail.com',
                'phone' => '2127611176',
                'street' => 'Rua Madame Mariana',
                'number' => '116',
                'complement' => null,
                'district' => 'Heliópolis',
                'city' => 'Belford Roxo',
                'state' => 'RJ',
                'postal_code' => '26123710',
                'contact' => 'Equipe de Comunicação',
                'contact_phone' => '2127611176',
                'contact_email' => 'equipecomunicacao.pibh@gmail.com',
                'notes' => 'Site: pibheliopolis.org.br | End.: Rua Madame Mariana, 116 - Heliópolis - Belford Roxo/RJ - CEP 26123-710 | Tel.: (21) 2761-1176 | Cultos (perfil público): Dom 8h30 e 18h | Qua 19h30',
                'logo' => 'https://yata-apix-a12c0cf0-3df6-47d2-95b4-5d3fee0f4766.s3-object.locaweb.com.br/e1f68fe4032f4cbcb93c0568c979563f.png',
            ]
        );

        Church::updateOrCreate(
            ['name' => 'Primeira Igreja Batista em Itaipu', 'city' => 'Niterói', 'state' => 'RJ'],
            [
                'pastor' => 'Pr. Marcos Caldeira Tristão',
                'email' => 'secretaria@pibitaipu.com.br',
                'phone' => '2127099149',
                'street' => 'Estrada Francisco da Cruz Nunes',
                'number' => '308',
                'complement' => null,
                'district' => 'Itaipu',
                'city' => 'Niterói',
                'state' => 'RJ',
                'postal_code' => '24355160',
                'contact' => 'Secretaria',
                'contact_phone' => '2127099149',
                'contact_email' => 'secretaria@pibitaipu.com.br',
                'notes' => 'Site: pibitaipu.com.br | End.: Estrada Francisco da Cruz Nunes, 308 - Itaipu - Niterói/RJ - CEP 24355-160 | Tel.: (21) 2709-9149 | Cultos (perfil público): Dom 10h30 e 19h30 | Qua 20h',
                'logo' => null,
            ]
        );

        Church::updateOrCreate(
            ['name' => 'Igreja Batista Memorial em Rondonópolis', 'city' => 'Rondonópolis', 'state' => 'MT'],
            [
                'pastor' => 'Pr. Cleverson Rodrigues',
                'email' => 'batistamemorialroo@gmail.com',
                'phone' => '66996911400',
                'street' => 'Avenida Guarapuava',
                'number' => '1087',
                'complement' => null,
                'district' => 'Jardim Iguassu',
                'city' => 'Rondonópolis',
                'state' => 'MT',
                'postal_code' => '78730312',
                'contact' => 'Secretaria',
                'contact_phone' => '66996911400',
                'contact_email' => 'batistamemorialroo@gmail.com',
                'notes' => 'Site: ibmroo.com | End.: Av. Guarapuava, 1087 - Jardim Iguassu - Rondonópolis/MT - CEP 78730-312 | Tel./WhatsApp: (66) 99691-1400 | Programação (perfil público): Dom EBD 8h45 | Cultos 10h e 19h | Qua 19h30 | Sex Pequenos Grupos 19h30',
                'logo' => null,
            ]
        );

        Church::updateOrCreate(
            [
                'name' => 'First Baptist Church Atlanta',
                'city' => 'Atlanta',
                'state' => 'GA',
            ],
            [
                'pastor' => 'Dr. Anthony George',
                'email' => null, // o site exibe o e-mail de forma ofuscada (não aparece completo publicamente)
                'phone' => '7702348300',

                'street' => 'North Peachtree Road',
                'number' => '4400',
                'complement' => null,
                'district' => 'Dunwoody',
                'city' => 'Atlanta',
                'state' => 'GA',
                'postal_code' => '30338',

                'contact' => 'Church Office',
                'contact_phone' => '7702348300',
                'contact_email' => null,

                'notes' => 'Site oficial: https://www.fba.org | Endereço oficial: 4400 North Peachtree Road, Atlanta, GA 30338 | Telefone oficial: 770.234.8300 | Senior Pastor: Dr. Anthony George | Fundada em 1848.',
                'logo' => null,
            ]
        );

        Church::factory()
            ->count(20)
            ->create();
    }
}
