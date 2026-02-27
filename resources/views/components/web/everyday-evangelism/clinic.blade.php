{{-- CLÍNICA (Evangelismo Eficaz) --}}
@push('css')
    <style>
        /* Fundo “prateado” com toque dourado (inspirado na marca EE) */
        .ee-silver-gold {
            background:
                radial-gradient(900px 520px at 15% 20%, rgba(241, 213, 122, .18), transparent 55%),
                radial-gradient(900px 520px at 85% 15%, rgba(199, 168, 64, .14), transparent 55%),
                linear-gradient(180deg, #ffffff 0%, #f7f8fb 55%, #f1f5f9 100%);
        }

        /* “Dourado EE” (mesma linha do botão do menu) */
        .ee-gold-btn {
            background: linear-gradient(135deg, #f1d57a 0%, #c7a840 48%, #8a7424 100%);
            color: #1b1709;
            border: 1px solid rgba(255, 255, 255, .35);
            text-shadow: 1px 1px 2px rgba(255, 255, 255, .75);
        }

        /* Hover sem “brilho estranho”: apenas leve realce e firmeza */
        .ee-gold-btn:hover {
            filter: brightness(1.05);
        }

        .ee-soft-border {
            border: 1px solid rgba(15, 23, 42, .10);
        }

        .ee-gold-stroke {
            background: linear-gradient(180deg, rgba(241, 213, 122, .0), rgba(199, 168, 64, .28), rgba(138, 116, 36, .18));
        }

        /* Pontos de leitura: pequeno “puxão” no ícone ao hover */
        .ee-step:hover .ee-step-icon {
            transform: translateX(2px);
        }
    </style>
@endpush

<section id="clinic" class="py-14 md:py-20 ee-silver-gold">
    <div class="px-4 max-w-8xl mx-auto sm:px-6 lg:px-8">
        {{-- Cabeçalho --}}
        <div class="max-w-5xl">
            <p class="text-xs font-extrabold tracking-widest uppercase text-slate-500">
                Formação de facilitadores • Evangelismo Eficaz
            </p>

            <h2 class="mt-3 text-3xl tracking-tight text-gray-800 md:text-4xl" style="font-family: 'Cinzel', serif;">
                Clínica de <span class="text-[#8a7424]">Evangelismo Eficaz</span>
            </h2>

            <p class="mt-4 leading-relaxed text-slate-700">
                As Clínicas de Evangelismo Eficaz são a evolução da clássica <strong>Clínica de EE</strong>: um evento
                intensivo
                em que líderes e futuros <strong>facilitadores</strong> vivenciam o método com ensino com Aulas e
                <strong>Saídas de Treinamento Prático (STP)</strong>, recebendo um modelo claro e replicável
                para reproduzir no contexto da igreja local.
            </p>
        </div>

        <div class="grid gap-10 mt-10 lg:grid-cols-2 lg:items-stretch">
            {{-- Coluna esquerda: conteúdo e “direção de leitura” --}}
            <div
                class="relative overflow-hidden bg-white rounded-3xl ee-soft-border shadow-[0_20px_50px_-30px_rgba(0,0,0,.35)]">
                {{-- detalhe lateral dourado --}}
                <div class="absolute inset-y-0 left-0 w-1 ee-gold-stroke"></div>

                <div class="p-6 sm:p-8">
                    <p class="text-sm font-extrabold tracking-wide text-slate-500">
                        Como a clínica funciona (na prática)
                    </p>

                    <div class="mt-6 space-y-4">
                        {{-- Step 1 --}}
                        <div class="flex gap-4 ee-step group">
                            <div
                                class="flex items-center justify-center w-10 h-10 px-3 rounded-2xl ee-soft-border bg-slate-50">
                                <span
                                    class="ee-step-icon transition-transform duration-200 text-[#8a7424] font-extrabold">1</span>
                            </div>
                            <div>
                                <h3 class="text-base font-extrabold text-slate-900">
                                    Workshop “O Evangelho Em Sua Mão”
                                </h3>
                                <p class="mt-1 leading-relaxed text-slate-700">
                                    A clínica inicia com o Workshop O Evangelho Em Sua Mão, que estabelece a base do
                                    treinamento e prepara o terreno para os níveis seguintes.
                                </p>
                            </div>
                        </div>

                        {{-- Step 2 --}}
                        <div class="flex gap-4 ee-step group">
                            <div
                                class="flex items-center justify-center w-10 h-10 px-3 rounded-2xl ee-soft-border bg-slate-50">
                                <span
                                    class="ee-step-icon transition-transform duration-200 text-[#8a7424] font-extrabold">2</span>
                            </div>
                            <div>
                                <h3 class="text-base font-extrabold text-slate-900">
                                    Facilitador (primeiro mentor credenciado)
                                </h3>
                                <p class="mt-1 leading-relaxed text-slate-700">
                                    Aqui acontece a mudança-chave do estilo de aula: o foco passa a ser o
                                    <strong>aluno</strong>.
                                    O professor torna-se <strong>facilitador</strong>, pois ele entra no barco com a
                                    turma,
                                    demonstra,
                                    corrige com ternura e conduz a prática enquanto todos fazem juntos, gerando
                                    aprendizagem
                                    autêntica (não apenas conteúdo).
                                </p>
                            </div>
                        </div>

                        {{-- Step 3 --}}
                        <div class="flex gap-4 ee-step group">
                            <div
                                class="flex items-center justify-center w-10 h-10 px-3 rounded-2xl ee-soft-border bg-slate-50">
                                <span
                                    class="ee-step-icon transition-transform duration-200 text-[#8a7424] font-extrabold">3</span>
                            </div>
                            <div>
                                <h3 class="text-base font-extrabold text-slate-900">
                                    Aulas + STPs (ninguém sai sozinho)
                                </h3>
                                <p class="mt-1 leading-relaxed text-slate-700">
                                    A clínica se distingue por unir ensino teórico e <strong>ensino prático</strong>. E,
                                    no método, os alunos <strong>nunca saem sozinhos</strong>: são acompanhados por
                                    <strong>mentores capacitados</strong>, que já conhecem o EE e sabem conduzir uma
                                    conversa evangelística.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- “Pílulas” de reforço --}}
                    <div class="flex flex-wrap gap-2 mt-8">
                        <span
                            class="px-3 py-1 text-sm font-bold rounded-full bg-slate-50 text-slate-800 ee-soft-border">
                            Ensino bíblico claro
                        </span>
                        <span
                            class="px-3 py-1 text-sm font-bold rounded-full bg-slate-50 text-slate-800 ee-soft-border">
                            STP supervisionada
                        </span>
                        <span
                            class="px-3 py-1 text-sm font-bold rounded-full bg-slate-50 text-slate-800 ee-soft-border">
                            Modelo replicável
                        </span>
                        <span
                            class="px-3 py-1 text-sm font-bold rounded-full bg-slate-50 text-slate-800 ee-soft-border">
                            Igreja local no centro
                        </span>
                    </div>

                </div>
            </div>

            {{-- Coluna direita: imagem + CTA --}}
            <div
                class="relative overflow-hidden bg-white rounded-3xl ee-soft-border shadow-[0_20px_50px_-30px_rgba(0,0,0,.35)]">
                <div class="p-6 sm:p-8">

                    <div class="flex sm:flex-row flex-col gap-4 justify-center items-center">
                        <img src="{{ asset('images/cover/facilitator.webp') }}"
                            class="obobject-contain h-full bg-white rounded-lg shadow max-w-40 ring-1 ring-slate-900/10 lg:order-2"
                            alt="Participantes em prática supervisionada durante uma Saída de Treinamento Prático (STP)"
                            loading="lazy" decoding="async" />

                        <div>
                            <p class="text-sm font-extrabold tracking-wide text-slate-500">
                                Convite e próximos passos
                            </p>

                            <h3 class="mt-2 text-xl font-extrabold tracking-tight text-slate-900 sm:text-2xl">
                                Traga sua liderança, e volte pronto para implementar
                            </h3>

                            <p class="mt-4 leading-relaxed text-slate-700">
                                Participar de uma Clínica de Evangelismo Eficaz acelera a implantação com segurança:
                                você vê o método funcionando, aprende o padrão, pratica no campo e retorna com um
                                caminho
                                claro para conduzir o treinamento na sua igreja.
                            </p>
                        </div>


                    </div>

                    {{-- Nota curta “clássica vs eficaz” --}}
                    <div class="p-4 mt-7 rounded-2xl bg-slate-50 ee-soft-border">
                        <p class="text-sm leading-relaxed text-slate-700">
                            <strong class="text-slate-900">Em resumo:</strong> a clínica é um evento intensivo de
                            <strong>03 dias</strong>, com prática e acompanhamento, para capacitar líderes a
                            <strong>treinar outros</strong> no contexto da igreja.
                        </p>
                    </div>

                    {{-- CTA --}}
                    <div class="grid gap-3 mt-7 sm:grid-cols-2">
                        <x-src.btn-gold label="Quero participar da próxima Clínica" route="#events" />
                        <x-src.btn-silver label="Falar com o EE-Brasil" data-open-wa />
                    </div>

                    <div class="mt-5 text-sm text-slate-600">
                        <p class="leading-relaxed">
                            Dica prática: a quantidade de participantes e o ritmo do treinamento local dependem do
                            número de <strong>mentores</strong> disponíveis, e é por isso que a clínica é tão
                            estratégica
                            para formar e alinhar a liderança.
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>
