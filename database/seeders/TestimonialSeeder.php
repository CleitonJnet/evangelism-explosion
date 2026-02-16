<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $testimonials = [
            [
                'name' => 'Pr. Joao Silva',
                'meta' => 'Igreja Batista - Fortaleza/CE',
                'quote' => 'O Evangelismo Explosivo trouxe clareza e direcao para nossa igreja. Hoje evangelizamos com conviccao, fidelidade biblica e acompanhamento.',
                'position' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Maria Oliveira',
                'meta' => 'Lider de Evangelismo - Niteroi/RJ',
                'quote' => 'Aprendi que evangelizar nao e apenas falar de Cristo, mas caminhar com pessoas. O EE me ensinou a discipular e mentorear outros.',
                'position' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Pr. Carlos Almeida',
                'meta' => 'Igreja Presbiteriana - Recife/PE',
                'quote' => 'O treinamento mudou a cultura da igreja. Saimos de acoes pontuais e passamos para um evangelismo intencional e continuo.',
                'position' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($testimonials as $testimonial) {
            Testimonial::query()->updateOrCreate(
                ['name' => $testimonial['name'], 'quote' => $testimonial['quote']],
                $testimonial,
            );
        }
    }
}
