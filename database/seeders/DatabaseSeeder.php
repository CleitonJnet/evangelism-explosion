<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Ministry;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call(ChurchesSeeder::class);
        $this->call(RolesSeeder::class);
        $this->call(UsersSeeder::class);

        Ministry::create(['id' => 1, 'initials' => 'ev²', 'name' => 'Evangelismo Eficaz', 'logo' => null, 'color' => null, 'description' => 'O Ministério Evangelismo Eficaz tem como propósito capacitar cristãos a compartilhar o evangelho de Jesus Cristo de forma clara, bíblica e intencional no dia a dia. Por meio de treinamentos práticos e fundamentados nas Escrituras, o ministério promove uma vida de testemunho fiel, preparando a igreja para cumprir a Grande Comissão com convicção, amor e simplicidade.']);
        Ministry::create(['id' => 2, 'initials' => 'ee-kids', 'name' => 'EE-Kids', 'logo' => null, 'color' => null, 'description' => 'O EE-Kids é o ministério voltado à evangelização de crianças, ensinando desde cedo as verdades fundamentais do evangelho de forma didática, bíblica e adequada à faixa etária. Seu objetivo é auxiliar pais, educadores e igrejas na formação espiritual das crianças, cultivando uma fé sólida e um coração disposto a conhecer e seguir a Cristo.']);

        Course::create(['id' => 1, 'order' => '0', 'execution' => 0, 'initials' => 'ev²',  'price' => '180,00', 'name' => 'Evangelismo Eficaz', 'learnMoreLink' => 'https://beta.eebrasil.org.br/ministry/everyday-evangelism', 'type' => 'Clínica', 'targetAudience' => 'A Clínica de Evangelismo Eficaz é destinada a pastores, líderes e membros da igreja local que desejam ser treinados para viver e compartilhar o evangelho de forma bíblica, intencional e multiplicadora no dia a dia.', 'certificate' => null, 'color' => null, 'description' => 'A Clínica de Evangelismo Explosivo capacita a igreja local a viver o evangelismo como estilo de vida, fortalecendo amizades intencionais, promovendo o evangelismo pessoal, estruturando o discipulado com acompanhamento e conduzindo a um crescimento saudável, no qual cada crente é preparado para testemunhar com fidelidade bíblica e compromisso com a Grande Comissão.', 'knowhow' => null, 'logo' => null, 'banner' => null, 'ministry_id' => 1]);
        Course::create(['id' => 2, 'order' => '1', 'execution' => 1, 'initials' => 'ESM',  'price' => '65,00', 'name' => 'Evangelho Em Sua Mão', 'learnMoreLink' => 'https://beta.eebrasil.org.br/ministry/everyday-evangelism', 'type' => 'Workshop', 'targetAudience' => 'A Implementação do Evangelismo Eficaz é destinada a membros da igreja local que desejam ser treinados para viver e compartilhar o evangelho de forma bíblica, intencional e multiplicadora no dia a dia.', 'certificate' => null, 'color' => null, 'description' => null, 'knowhow' => null, 'logo' => null, 'banner' => null, 'ministry_id' => 1]);
        Course::create(['id' => 3, 'order' => '2', 'execution' => 1, 'initials' => 'e²',   'price' => '22,00', 'name' => 'Explicar o Evangelho', 'learnMoreLink' => 'https://beta.eebrasil.org.br/ministry/everyday-evangelism', 'type' => 'implementation', 'targetAudience' => 'A Implementação do Evangelismo Eficaz é destinada a membros da igreja local que desejam ser treinados para viver e compartilhar o evangelho de forma bíblica, intencional e multiplicadora no dia a dia.', 'certificate' => null, 'color' => null, 'description' => null, 'knowhow' => null, 'logo' => null, 'banner' => null, 'ministry_id' => 1]);
        Course::create(['id' => 4, 'order' => '3', 'execution' => 1, 'initials' => 'm²',   'price' => '18,00', 'name' => 'Mentorear para Multiplicar', 'learnMoreLink' => 'https://beta.eebrasil.org.br/ministry/everyday-evangelism', 'type' => 'implementation', 'targetAudience' => 'A Implementação do Evangelismo Eficaz é destinada a membros da igreja local que desejam ser treinados para viver e compartilhar o evangelho de forma bíblica, intencional e multiplicadora no dia a dia.', 'certificate' => null, 'color' => null, 'description' => null, 'knowhow' => null, 'logo' => null, 'banner' => null, 'ministry_id' => 1]);
        Course::create(['id' => 5, 'order' => '4', 'execution' => 1, 'initials' => 'c²',   'price' => '18,00', 'name' => 'Crescer em Cristo', 'learnMoreLink' => 'https://beta.eebrasil.org.br/ministry/everyday-evangelism', 'type' => 'implementation', 'targetAudience' => 'A Implementação do Evangelismo Eficaz é destinada a membros da igreja local que desejam ser treinados para viver e compartilhar o evangelho de forma bíblica, intencional e multiplicadora no dia a dia.', 'certificate' => null, 'color' => null, 'description' => null, 'knowhow' => null, 'logo' => null, 'banner' => null, 'ministry_id' => 1]);
        Course::create(['id' => 6, 'order' => '5', 'execution' => 1, 'initials' => 'r²',   'price' => '18,00', 'name' => 'Responder com a Razão', 'learnMoreLink' => 'https://beta.eebrasil.org.br/ministry/everyday-evangelism', 'type' => 'implementation', 'targetAudience' => 'A Implementação do Evangelismo Eficaz é destinada a membros da igreja local que desejam ser treinados para viver e compartilhar o evangelho de forma bíblica, intencional e multiplicadora no dia a dia.', 'certificate' => null, 'color' => null, 'description' => null, 'knowhow' => null, 'logo' => null, 'banner' => null, 'ministry_id' => 1]);

        Course::create(['id' => 7, 'order' => '0', 'execution' => 0, 'initials' => 'EPC',  'price' => '50,00', 'name' => 'Esperança Para Crianças', 'learnMoreLink' => 'https://beta.eebrasil.org.br/ministry/kids-ee', 'type' => 'Workshop', 'targetAudience' => 'O Workshop Hope for Kids (EE-Kids) é destinado a professores, líderes de ministério infantil, pais e voluntários que desejam ser capacitados para apresentar o evangelho às crianças de forma bíblica, clara e adequada à sua faixa etária.', 'certificate' => null, 'color' => null, 'description' => 'O EE-Kids, por meio da ferramenta Esperança Para Crianças, capacita a igreja local a apresentar o evangelho de Cristo às crianças de forma clara, bíblica e apropriada à sua idade, envolvendo família e comunidade no processo. Com metodologia simples e fiel às Escrituras, o ministério forma líderes e voluntários para comunicar a mensagem da salvação, cultivar valores cristãos e estabelecer fundamentos de discipulado desde cedo, cooperando para o crescimento saudável da igreja. Em suma: sem atalhos, sem modismos, o bom e antigo evangelho, semeado nos corações pequenos para frutos eternos.', 'knowhow' => null, 'logo' => null, 'banner' => null, 'ministry_id' => 2]);
        Course::create(['id' => 8, 'order' => '1', 'execution' => 1, 'initials' => 'EPC',  'price' => '10,00', 'name' => 'Esperança Para Crianças', 'learnMoreLink' => 'https://beta.eebrasil.org.br/ministry/kids-ee', 'type' => 'implementation', 'targetAudience' => 'O Workshop Hope for Kids (EE-Kids) é destinado a professores, líderes de ministério infantil, pais e voluntários que desejam ser capacitados para apresentar o evangelho às crianças de forma bíblica, clara e adequada à sua faixa etária.', 'certificate' => null, 'color' => null, 'description' => null, 'knowhow' => null, 'logo' => null, 'banner' => null, 'ministry_id' => 2]);

        $this->call(SectionSeeder::class);

        DB::table('course_user')->insert(['course_id' => 1]);
        User::findOrFail(1)->courseAsTeacher()->sync([1, 2, 3, 4, 5, 6, 7, 8]);

        $this->call(TrainingsSeeder::class);
    }
}
