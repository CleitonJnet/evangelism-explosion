@push('css')
    <style>
        /* ================= EE • Tema Metal (prata) + Ouro (antigo) ================= */

        :root {
            --ee-gold-1: #F3E7C2;
            /* champagne */
            --ee-gold-2: #D8BE74;
            /* ouro antigo */
            --ee-gold-3: #A88126;
            /* ouro escuro */
            --ee-silver-1: #F8FAFC;
            /* slate-50 */
            --ee-silver-2: #EEF2F7;
            /* silver */
            --ee-silver-3: #D7DEE8;
            /* silver edge */
            --ee-ink: #0F172A;
            /* slate-900 */
            --ee-muted: #475569;
            /* slate-600 */
        }

        /* Cartões “metal” */
        .ee-metal-card {
            background:
                linear-gradient(135deg, rgba(255, 255, 255, .92), rgba(238, 242, 247, .70));
            border: 1px solid rgba(15, 23, 42, .10);
            box-shadow:
                0 18px 40px rgba(2, 6, 23, .08),
                inset 0 1px 0 rgba(255, 255, 255, .70);
        }

        /* Filete dourado (assinatura visual) */
        .ee-gold-edge {
            background: linear-gradient(180deg, var(--ee-gold-1), var(--ee-gold-2), var(--ee-gold-3));
            box-shadow: 0 10px 26px rgba(168, 129, 38, .18);
        }

        /* Badge/tags em prata com borda dourada sutil */
        .ee-chip {
            background: linear-gradient(135deg, rgba(255, 255, 255, .85), rgba(238, 242, 247, .70));
            border: 1px solid rgba(168, 129, 38, .22);
            color: #7a5b1a;
        }

        /* Título com “ouro discreto”, sem amarelo forte */
        .ee-gold-text {
            background: linear-gradient(90deg, #7a5b1a, var(--ee-gold-3), #caa14a);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        /* “Guia de leitura”: leve animação (sem brilho) */
        .ee-reading-dot {
            width: 10px;
            height: 10px;
            border-radius: 9999px;
            background: linear-gradient(135deg, var(--ee-gold-1), var(--ee-gold-3));
            box-shadow: 0 10px 22px rgba(168, 129, 38, .14);
        }

        @keyframes ee-nudge {

            0%,
            100% {
                transform: translateY(0);
                opacity: .85;
            }

            50% {
                transform: translateY(-3px);
                opacity: 1;
            }
        }

        .ee-nudge {
            animation: ee-nudge 2.2s ease-in-out infinite;
        }

        @media (prefers-reduced-motion: reduce) {
            .ee-nudge {
                animation: none !important;
            }
        }

        /* Remover qualquer “glow estranho” de hover:
                                                                                                                                                                                                                                                                                                                                                                               (aqui evitamos filter e box-shadow agressivos no hover) */
        .ee-soft-hover:hover {
            transform: translateY(-1px);
        }

        .ee-soft-hover {
            transition: transform .18s ease, background-color .18s ease, border-color .18s ease;
        }
    </style>
@endpush

@php
    // Cores por nível/módulo (referência didática; use como “pontos” e “filetes”, não como bloco gigante)
    $levels = [
        [
            'key' => 'workshop',
            'name' => 'Workshop (ESM)',
            'hex' => '#D8BE74',
            'desc' => 'Fundamentos e visão: o evangelho em suas mãos.',
        ],
        [
            'key' => 'e2',
            'name' => 'e² • Explicar o Evangelho',
            'hex' => '#5BC3CD',
            'desc' => 'Clareza e estrutura: comunicar o evangelho com simplicidade.',
        ],
        [
            'key' => 'm2',
            'name' => 'm² • Mentorear para Multiplicar',
            'hex' => '#E87906',
            'desc' => 'Mentoria intencional: formar discipuladores.',
        ],
        [
            'key' => 'c2',
            'name' => 'c² • Crescer em Cristo',
            'hex' => '#2E7D32',
            'desc' => 'Bases de crescimento: hábitos e meios de graça na vida cristã.',
        ],
        [
            'key' => 'r2',
            'name' => 'r² • Responder com a Razão',
            'hex' => '#20307A',
            'desc' => 'Objeções e diálogo: responder com mansidão e firmeza.',
        ],
    ];
@endphp

<section id="o-que-e" class="pt-6 pb-12 ee-metal-section md:pb-20">
    <div class="px-4 max-w-8xl mx-auto sm:px-6 lg:px-8">

        {{-- Cabeçalho --}}
        <div class="max-w-5xl">
            <h2 class="mt-5 text-2xl tracking-tight sm:text-3xl" style="font-family:'Cinzel',serif;">
                Uma <span class="font-semibold text-sky-950">metodologia</span> de <span class="ee-gold-text">aprendizagem
                    autêntica</span>,
                com prática supervisionada (STP) e <span class="font-semibold">discipulado contínuo</span>
            </h2>

            <p class="max-w-3xl mt-4 leading-relaxed text-slate-700">
                Evangelismo Eficaz não é <strong>“mais um curso”</strong>: é uma ferramenta ministerial em que a igreja
                aprende <strong>fazendo</strong>, semana após semana, até que evangelismo e discipulado se tornem uma
                cultura.
            </p>
        </div>

        <div class="grid gap-10 mt-10 lg:grid-cols-12">

            {{-- Coluna esquerda: o coração do método --}}
            <div class="lg:col-span-12">
                <div class="relative p-6 overflow-hidden sm:p-8 rounded-3xl ee-metal-card">
                    <span class="absolute left-0 top-0 h-2/5 w-1.5 rounded-e-full ee-gold-edge"></span>

                    <p class="text-xs font-extrabold tracking-widest uppercase text-slate-600">
                        Como a igreja aprende
                    </p>

                    <h3 class="mt-2 text-xl font-extrabold tracking-tight sm:text-2xl text-slate-900">
                        O professor é um <span class="ee-gold-text">facilitador</span>, e como mentor, ele entra no
                        barco com os alunos
                    </h3>

                    <p class="mt-4 leading-relaxed text-slate-700">
                        Durante o treinamento, o papel clássico de “professor-treinador” muda para o de
                        <strong>facilitador</strong>: ele deixa de ser um “guia turístico” que apenas aponta o caminho e
                        passa a agir como um “instrutor de rafting”, que entra no bote com os alunos e participa das
                        descobertas na prática.
                    </p>

                    <div class="grid gap-4 mt-6 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="p-4 rounded-2xl bg-white/60 ring-1 ring-slate-900/10 ee-soft-hover">
                            <p class="text-sm font-extrabold text-slate-900">Foco no aluno</p>
                            <p class="mt-1 text-sm leading-relaxed text-slate-700">
                                Menos palestra, mais interação: o conteúdo se fixa enquanto o aluno pratica e
                                testemunha.
                            </p>
                        </div>

                        <div class="p-4 rounded-2xl bg-white/60 ring-1 ring-slate-900/10 ee-soft-hover">
                            <p class="text-sm font-extrabold text-slate-900">Aprender fazendo</p>
                            <p class="mt-1 text-sm leading-relaxed text-slate-700">
                                A sala de aula prepara, mas é na STP que a aprendizagem se torna autêntica.
                            </p>
                        </div>

                        <div class="p-4 rounded-2xl bg-white/60 ring-1 ring-slate-900/10 ee-soft-hover">
                            <p class="text-sm font-extrabold text-slate-900">STP com mentoria</p>
                            <p class="mt-1 text-sm leading-relaxed text-slate-700">
                                Os alunos <strong>não saem sozinhos</strong>: sempre há um mentor capacitado conduzindo
                                a equipe.
                            </p>
                        </div>

                        <div class="p-4 rounded-2xl bg-white/60 ring-1 ring-slate-900/10 ee-soft-hover">
                            <p class="text-sm font-extrabold text-slate-900">Testemunho + Relatório Público</p>
                            <p class="mt-1 text-sm leading-relaxed text-slate-700">
                                A cada semana, a experiência volta para a classe: o encorajamento acelera o
                                crescimento.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Coluna direita: pilares + 5 níveis + ciclo --}}
            <div class="gap-8 lg:col-span-12 lg:grid lg:grid-cols-2">

                {{-- Pilares --}}
                <div id="pilares" class="p-6 sm:p-8 rounded-3xl ee-metal-card">
                    <div class="flex items-start gap-3">
                        <span class="mt-2 ee-reading-dot ee-nudge"></span>
                        <div>
                            <p class="text-xs font-extrabold tracking-widest uppercase text-slate-600">
                                Pilares da metodologia
                            </p>
                            <h3 class="mt-1 text-xl font-extrabold tracking-tight text-slate-900">
                                Ritmo simples, repetível e pastoral
                            </h3>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 mt-5">
                        <div class="p-4 rounded-2xl bg-white/60 ring-1 ring-slate-900/10 ee-soft-hover">
                            <p class="font-extrabold text-slate-900">1) Aula participativa</p>
                            <p class="mt-1 text-sm text-slate-700">
                                O facilitador conduz discussões e modela o processo, sem monopolizar a fala.
                            </p>
                        </div>

                        <div class="p-4 rounded-2xl bg-white/60 ring-1 ring-slate-900/10 ee-soft-hover">
                            <p class="font-extrabold text-slate-900">2) STP em equipe</p>
                            <p class="mt-1 text-sm text-slate-700">
                                A equipe é formada por <strong>um mentor e dois alunos</strong>, e o mentor dirige a
                                prática.
                            </p>
                        </div>

                        <div class="p-4 rounded-2xl bg-white/60 ring-1 ring-slate-900/10 ee-soft-hover">
                            <p class="font-extrabold text-slate-900">3) Mentores capacitados</p>
                            <p class="mt-1 text-sm text-slate-700">
                                Treinamento de e² exige presença e acompanhamento de mentores que já vivem o método.
                            </p>
                        </div>

                        <div class="p-4 rounded-2xl bg-white/60 ring-1 ring-slate-900/10 ee-soft-hover">
                            <p class="font-extrabold text-slate-900">4) Relatórios de acompanhamento</p>
                            <p class="mt-1 text-sm text-slate-700">
                                A prática volta para a classe: relatórios de encorajamento, correção de rumos e avanço.
                            </p>
                        </div>
                    </div>
                </div>

                <figure
                    class="mt-8 overflow-hidden shadow-inner rounded-2xl ring-1 ring-slate-900/10 bg-white/70 lg:mt-0">
                    <img src="{{ asset('images/class-e2-unit-5.webp') }}" class="object-cover w-full max-h-96"
                        alt="Saída de Treinamento Prático" loading="lazy" decoding="async" />
                </figure>
            </div>

        </div>
    </div>
</section>
