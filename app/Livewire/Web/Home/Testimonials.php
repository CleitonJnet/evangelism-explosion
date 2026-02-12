<?php

namespace App\Livewire\Web\Home;

use Livewire\Component;

class Testimonials extends Component
{
    public function render()
    {
        $testimonials = [
            [
                'photo' => 'https://placehold.co/900x1100',
                'quote' => 'O Evangelismo Explosivo trouxe clareza e direção para nossa igreja. Hoje evangelizamos com convicção, fidelidade bíblica e acompanhamento.',
                'name' => 'Pr. João Silva',
                'meta' => 'Igreja Batista — Fortaleza/CE',
            ],
            [
                'photo' => 'https://placehold.co/900x1100',
                'quote' => 'Aprendi que evangelizar não é apenas falar de Cristo, mas caminhar com pessoas. O EE me ensinou a discipular e mentorear outros.',
                'name' => 'Maria Oliveira',
                'meta' => 'Líder de Evangelismo — Niterói/RJ',
            ],
            [
                'photo' => 'https://placehold.co/900x1100',
                'quote' => 'O treinamento mudou a cultura da igreja. Saímos de ações pontuais e passamos para um evangelismo intencional e contínuo.',
                'name' => 'Pr. Carlos Almeida',
                'meta' => 'Igreja Presbiteriana — Recife/PE',
            ],
            [
                'photo' => 'https://placehold.co/900x1100',
                'quote' => 'O Evangelismo Explosivo trouxe clareza e direção para nossa igreja. Hoje evangelizamos com convicção, fidelidade bíblica e acompanhamento.',
                'name' => 'Pr. João Silva',
                'meta' => 'Igreja Batista — Fortaleza/CE',
            ],
            [
                'photo' => 'https://placehold.co/900x1100',
                'quote' => 'Aprendi que evangelizar não é apenas falar de Cristo, mas caminhar com pessoas. O EE me ensinou a discipular e mentorear outros.',
                'name' => 'Maria Oliveira',
                'meta' => 'Líder de Evangelismo — Niterói/RJ',
            ],
            [
                'photo' => 'https://placehold.co/900x1100',
                'quote' => 'O treinamento mudou a cultura da igreja. Saímos de ações pontuais e passamos para um evangelismo intencional e contínuo.',
                'name' => 'Pr. Carlos Almeida',
                'meta' => 'Igreja Presbiteriana — Recife/PE',
            ],
        ];

        return view('livewire.web.home.testimonials', compact('testimonials'));
    }
}
