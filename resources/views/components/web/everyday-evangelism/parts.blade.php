@php
    $levels = [
        'e2' => [
            'part' => 'Parte 2: Ensine',
            'title' => 'Explicar o <div>Evangelho</div>',
            'code' => 'e²',
            'color' => '#55c5d0',
            'cover' => '/images/cover/e2.webp',
            'logo' => '/images/logo/explaining-the-gospel.webp',
            'text' =>
                'Aprofunda a prática da evangelização por meio das Saídas de Treinamento Prático, ensinando o participante a conduzir uma explicação clara, bíblica e natural do Evangelho, com sensibilidade espiritual e confiança.',
        ],
        'm2' => [
            'part' => 'Parte 3: Multiplique',
            'title' => 'Mentorear para <div>Multiplicar</div>',
            'code' => 'm²',
            'color' => '#f57315',
            'cover' => '/images/cover/m2.webp',
            'logo' => '/images/logo/mentoring-for-multiplication.webp',
            'text' =>
                'Forma mentores que acompanham novos evangelistas do e², estruturando duplas para as Saídas de Treinamento Prático e promovendo uma multiplicação saudável e intencional de discípulos na igreja.',
        ],
        'c2' => [
            'part' => 'Parte 4: Discipule',
            'title' => 'Crescer em <div>Cristo</div>',
            'code' => 'c²',
            'color' => '#efce1d',
            'cover' => '/images/cover/c2.webp',
            'logo' => '/images/logo/means-growth.webp',
            'text' =>
                'Material de discipulado para novos convertidos que consolida a fé cristã por meio de fundamentos essenciais da vida cristã, preparando o discípulo para integração plena na igreja e retorno ao ciclo do treinamento.',
        ],
        'r2' => [
            'part' => 'Parte 5: Responda',
            'title' => 'Responder com a <div>Razão</div>',
            'code' => 'r²',
            'color' => '#5a5a5a',
            'cover' => '/images/cover/r2.webp',
            'logo' => '/images/logo/reasonable-answers.webp',
            'text' =>
                'Treinamento apologético que capacita o cristão a responder, de forma bíblica e amorosa, às principais perguntas e objeções à fé cristã, fortalecendo a convicção pessoal e a evangelização em contextos desafiadores.',
        ],
    ];

@endphp

<section id="parts" class="pb-12 pt-14 bg-linear-to-b from-slate-200 via-slate-50 to-slate-300 md:pb-16 md:pt-20">
    <style>
        .ee-reveal {
            opacity: 0;
            transform: translateY(14px);
            animation: eeFadeUp .6s ease-out forwards;
            animation-delay: .08s;
        }

        @keyframes eeFadeUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ========= Brilho dourado sutil no marcador ========= */
        .ee-gold-pulse {
            position: relative;
            overflow: hidden;
        }

        /* leve “respiração” */
        .ee-gold-pulse {
            animation: eeGlow 1.8s ease-in-out infinite;
        }

        @keyframes eeGlow {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(199, 168, 64, 0);
            }

            50% {
                box-shadow: 0 0 2px 1px rgb(210, 161, 0);
            }
        }

        /* “sheen” (reflexo passando) — bem discreto */
        .ee-gold-pulse::after {
            content: "";
            position: absolute;
            inset: -40% -80%;
            background: linear-gradient(90deg,
                    transparent 0%,
                    rgba(255, 255, 255, .25) 45%,
                    rgba(255, 255, 255, .05) 55%,
                    transparent 100%);
            transform: rotate(18deg) translateX(-60%);
            animation: eeSheen 4.5s ease-in-out infinite;
        }

        @keyframes eeSheen {

            0%,
            60% {
                transform: rotate(18deg) translateX(-70%);
                opacity: 0;
            }

            70% {
                opacity: 1;
            }

            85% {
                transform: rotate(18deg) translateX(70%);
                opacity: 0.8;
            }

            100% {
                transform: rotate(18deg) translateX(70%);
                opacity: 0;
            }
        }

        /* ========= Acessibilidade: respeita quem prefere menos animação ========= */
        @media (prefers-reduced-motion: reduce) {

            .ee-reveal,
            .ee-gold-pulse,
            .ee-gold-pulse::after {
                animation: none !important;
                transform: none !important;
                opacity: 1 !important;
            }
        }
    </style>

    <div class="px-4 max-w-8xl mx-auto sm:px-6 lg:px-8">

        <div class="max-w-3xl mx-auto mb-12 text-center">
            <h2 class="text-3xl tracking-tight text-gray-800 md:text-4xl" style="font-family: 'Cinzel', serif;">
                As 5 partes do <span class="text-[#8a7424]">Evangelismo Eficaz</span>
            </h2>

            {{-- Linha dourada sutil --}}
            <div class="mx-auto mt-4 h-0.75 w-52 rounded-full"
                style="background: linear-gradient(90deg,
                    rgba(138,116,36,.25),
                    rgba(199,168,64,.75),
                    rgba(241,213,122,.25));">
            </div>

            <p class="mt-4 text-base leading-relaxed text-gray-600 md:text-lg">
                Um ciclo contínuo: evangelismo, prática, multiplicação e discipulado.
            </p>
        </div>

        <x-web.everyday-evangelism.part-1-syfw />

        <div class="max-w-4xl mx-auto my-10 md:my-16 ee-reveal">
            <div class="p-6 md:p-8">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:gap-6">

                    <div class="w-4 h-4 mt-1 shrink-0 rounded-2xl ring-1 ring-black/10 ee-gold-pulse"
                        style="background: linear-gradient(135deg,
                        rgba(138,116,36,.20),
                        rgba(199,168,64,.55),
                        rgba(241,213,122,.25));">
                    </div>

                    <div class="text-center md:text-left">
                        <p class="text-base font-semibold leading-relaxed text-slate-800 md:text-lg">
                            As 4 partes abaixo são normalmente ministradas em <strong>sete sessões semanais</strong>,
                            combinando ensino bíblico, prática supervisionada e acompanhamento ministerial.
                        </p>

                        <p class="mt-3 text-sm leading-relaxed text-slate-600 md:text-base">
                            Esses treinamentos são conduzidos por <strong>facilitadores da igreja local</strong>
                            devidamente credenciados em clínica, garantindo fidelidade doutrinária, clareza metodológica
                            e aplicação prática no contexto da igreja local, sempre integrados às
                            <strong>Saídas de Treinamento Prático (STP)</strong>.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Grade 2x2 (mantém sua estrutura, só com “acabamento” mais do site) --}}
        <div class="mx-auto max-w-8xl">
            <div class="grid grid-cols-1 overflow-hidden bg-white rounded-3xl ring-1 ring-slate-900/10 md:grid-cols-2"
                {{-- style="box-shadow: -25px 1px 10px -25px rgba(0,0,0,0.75) inset, 25px 1px 10px -25px rgba(0,0,0,0.75) inset;
                    -webkit-box-shadow: -25px 1px 10px -25px rgba(0,0,0,0.75) inset, 25px 1px 10px -25px rgba(0,0,0,0.75) inset;
                    -moz-box-shadow: -25px 1px 10px -25px rgba(0,0,0,0.75) inset, 25px 1px 10px -25px rgba(0,0,0,0.75) inset;" --}}>

                {{-- Card 2 --}}
                <x-web.everyday-evangelism.cycle-card :part="$levels['e2']['part']" :title="$levels['e2']['title']" :code="$levels['e2']['code']"
                    :color="$levels['e2']['color']" :cover="$levels['e2']['cover']" :logo="$levels['e2']['logo']" :text="$levels['e2']['text']" side="right" />

                {{-- Card 3 --}}
                <x-web.everyday-evangelism.cycle-card :part="$levels['m2']['part']" :title="$levels['m2']['title']" :code="$levels['m2']['code']"
                    :color="$levels['m2']['color']" :cover="$levels['m2']['cover']" :logo="$levels['m2']['logo']" :text="$levels['m2']['text']" side="left" />

                {{-- Card 4 --}}
                <x-web.everyday-evangelism.cycle-card :part="$levels['c2']['part']" :title="$levels['c2']['title']" :code="$levels['c2']['code']"
                    :color="$levels['c2']['color']" :cover="$levels['c2']['cover']" :logo="$levels['c2']['logo']" :text="$levels['c2']['text']" side="right" />

                {{-- Card 5 --}}
                <x-web.everyday-evangelism.cycle-card :part="$levels['r2']['part']" :title="$levels['r2']['title']" :code="$levels['r2']['code']"
                    :color="$levels['r2']['color']" :cover="$levels['r2']['cover']" :logo="$levels['r2']['logo']" :text="$levels['r2']['text']" side="right" />
            </div>

            {{-- Dica de ciclo --}}
            <div class="max-w-4xl mx-auto mt-10 md:text-left">
                <p class="text-sm font-semibold leading-relaxed text-center text-sky-950 md:text-base"
                    style="text-shadow: 1px 0 2px #fff">
                    O ciclo inicia-se no Workshop, desenvolve-se na prática, consolida-se no discipulado e, então,
                    reinicia-se como um processo contínuo de Crescimento Saudável.
                </p>
            </div>
        </div>
    </div>
</section>
