@php
    // Metadados para a página de visão e missão
    $title = 'Visão, Missão & Principios';
    $description =
        'Descubra a visão e missão do Evangelismo Explosivo: equipar cristãos para testemunhar e multiplicar discípulos em todas as nações, transformando vidas através do poder do Evangelho.';
    $keywords = 'visão evangelismo explosivo, missão evangelismo explosivo, propósito, multiplicação, treinamento';
    $ogImage = asset('images/og/vision.webp');
@endphp

<x-layouts.guest :title="$title" :description="$description" :keywords="$keywords" :ogImage="$ogImage">
    <x-web.header :title="$title" subtitle='Nossa visão e missão como ministério e os princípios que nos guiam'
        :cover="asset('images/3rd_nations_congress_2016.webp')" />

    {{-- Conteúdo principal envolto em container para harmonizar com o tema --}}
    <x-web.container class="my-10">
        {{-- VISÃO (estilo institucional conforme PDF) --}}
        <section
            class="relative mb-10 overflow-hidden border shadow-md rounded-2xl bg-white/95 border-amber-500/85 shadow-black/10">

            {{-- Fundo mapa --}}
            <div class="absolute inset-0 pointer-events-none opacity-[0.08]"
                style="background-image:url('{{ asset('images/3rd_nations_congress_2016.webp') }}');
               background-size:cover;
               background-position:center;">
            </div>

            <div class="relative z-10 px-6 text-center py-14">

                <h2 class="mb-6 text-4xl font-semibold tracking-wide text-[#8a7424]" style="font-family:'Cinzel',serif;">
                    VISÃO
                </h2>

                <p class="max-w-4xl mx-auto text-lg leading-relaxed uppercase text-slate-800">
                    CADA NAÇÃO CAPACITANDO CADA GRUPO ÉTNICO E CADA FAIXA ETÁRIA A
                    TESTEMUNHAR A TODAS AS PESSOAS
                </p>

                {{-- divisor --}}
                <div class="w-32 h-0.5 mx-auto mt-8 bg-linear-to-r from-[#8a7424] via-[#c7a840] to-[#f1d57a]">
                </div>
            </div>
        </section>

        {{-- MISSÃO (estilo institucional conforme PDF) --}}
        <section
            class="relative overflow-hidden border shadow-md rounded-2xl bg-white/95 border-amber-500/85 shadow-black/10">

            {{-- Fundo mapa --}}
            <div class="absolute inset-0 pointer-events-none opacity-[0.08]"
                style="background-image:url('{{ asset('images/3rd_nations_congress_2016.webp') }}');
               background-size:cover;
               background-position:center;">
            </div>

            <div class="relative z-10 px-6 text-center py-14">

                <h2 class="mb-6 text-4xl font-semibold tracking-wide text-[#8a7424]"
                    style="font-family:'Cinzel',serif;">
                    MISSÃO
                </h2>

                <p class="max-w-4xl mx-auto text-lg leading-relaxed uppercase text-slate-800">
                    GLORIFICAR A DEUS TREINANDO CRENTES DE TODAS AS FAIXAS ETÁRIAS,
                    NAS IGREJAS LOCAIS, PARA EVANGELIZAÇÃO PESSOAL E DISCIPULADO.
                </p>

                {{-- divisor --}}
                <div class="w-32 h-0.5 mx-auto mt-8 bg-linear-to-r from-[#8a7424] via-[#c7a840] to-[#f1d57a]"></div>
            </div>
        </section>
        </x-webweb.container>
        <x-web.container>
            {{-- Seção Princípios --}}
            <section class="mb-4">
                <h3 class="text-2xl sm:text-3xl lg:text-4xl text-[#8a7424] mb-6" style="font-family: 'Cinzel', serif;">
                    5 Princípios Bíblicos Chave
                </h3>

                <p class="leading-relaxed text-[#574815]">
                    Estes princípios guiam a prática do Evangelismo Explosivo, fundamentados
                    nas Escrituras, para que todo o ministério seja alinhado à visão do chamado à Grande Comissão.
                </p>
                <div class="grid grid-cols-1 gap-6 mt-6 sm:grid-cols-2 md:grid-cols-3 2md:grid-cols-4 lg:grid-cols-5">
                    {{-- Card 1: Participação --}}
                    <div
                        class="flex flex-col overflow-hidden border shadow-md bg-white/95 border-amber-500/85 shadow-black/10 rounded-2xl">
                        <img src="{{ asset('images/principle/1.png') }}" alt="Princípio da participação"
                            class="object-cover w-full h-40">
                        <div class="flex flex-col flex-1 p-4 text-center">
                            <h3 class="mb-2 text-lg text-[#8a7424]" style="font-family: 'Cinzel', serif;">
                                Princípio da <div class="font-bold">Participação</div>
                            </h3>
                            <p class="flex-1 leading-relaxed text-slate-600">É uma responsabilidade e um
                                privilégio de
                                cada
                                crente fazer o trabalho de evangelismo.</p>
                        </div>
                        <div class="h-1 w-full bg-linear-to-r from-[#f1d57a] via-[#c7a840] to-[#8a7424]"></div>
                    </div>
                    {{-- Card 2: Multiplicação --}}
                    <div
                        class="flex flex-col overflow-hidden border shadow-md bg-white/95 border-amber-500/85 shadow-black/10 rounded-2xl">
                        <img src="{{ asset('images/principle/2.png') }}" alt="Princípio da multiplicação"
                            class="object-cover w-full h-40">
                        <div class="flex flex-col flex-1 p-4 text-center">
                            <h3 class="mb-2 text-lg text-[#8a7424]" style="font-family: 'Cinzel', serif;">
                                Princípio da <div class="font-bold">Multiplicação</div>
                            </h3>
                            <p class="flex-1 leading-relaxed text-slate-600">É mais frutífero treinar um
                                ganhador de
                                almas
                                do que apenas ganhar uma alma.</p>
                        </div>
                        <div class="h-1 w-full bg-linear-to-r from-[#f1d57a] via-[#c7a840] to-[#8a7424]"></div>
                    </div>
                    {{-- Card 3: Demonstração --}}
                    <div
                        class="flex flex-col overflow-hidden border shadow-md bg-white/95 border-amber-500/85 shadow-black/10 rounded-2xl">
                        <img src="{{ asset('images/principle/3.png') }}" alt="Princípio da demonstração"
                            class="object-cover w-full h-40">
                        <div class="flex flex-col flex-1 p-4 text-center">
                            <h3 class="mb-2 text-lg text-[#8a7424]" style="font-family: 'Cinzel', serif;">
                                Princípio da <div class="font-bold">Demonstração</div>
                            </h3>
                            <p class="flex-1 leading-relaxed text-slate-600">O treinamento de ganhadores de
                                almas é
                                melhor
                                realizado através de Saídas de Treinamento Prático (STP).</p>
                        </div>
                        <div class="h-1 w-full bg-linear-to-r from-[#f1d57a] via-[#c7a840] to-[#8a7424]"></div>
                    </div>
                    {{-- Card 4: Delegação --}}
                    <div
                        class="flex flex-col overflow-hidden border shadow-md bg-white/95 border-amber-500/85 shadow-black/10 rounded-2xl">
                        <img src="{{ asset('images/principle/4.png') }}" alt="Princípio da delegação"
                            class="object-cover w-full h-40">
                        <div class="flex flex-col flex-1 p-4 text-center">
                            <h3 class="mb-2 text-lg text-[#8a7424]" style="font-family: 'Cinzel', serif;">
                                Princípio da <div class="font-bold">Delegação</div>
                            </h3>
                            <p class="flex-1 leading-relaxed text-slate-600">É tarefa do pastor equipar os
                                crentes para
                                o
                                trabalho do ministério.</p>
                        </div>
                        <div class="h-1 w-full bg-linear-to-r from-[#f1d57a] via-[#c7a840] to-[#8a7424]"></div>
                    </div>
                    {{-- Card 5: Oração --}}
                    <div
                        class="flex flex-col overflow-hidden border shadow-md bg-white/95 border-amber-500/85 shadow-black/10 rounded-2xl">
                        <img src="{{ asset('images/principle/5.png') }}" alt="Princípio da oração"
                            class="object-cover w-full h-40">
                        <div class="flex flex-col flex-1 p-4 text-center">
                            <h3 class="mb-2 text-lg text-[#8a7424]" style="font-family: 'Cinzel', serif;">
                                Princípio da <div class="font-bold">Oração</div>
                            </h3>
                            <p class="flex-1 leading-relaxed text-slate-600">Evangelismo sem oração é
                                presunção.</p>
                        </div>
                        <div class="h-1 w-full bg-linear-to-r from-[#f1d57a] via-[#c7a840] to-[#8a7424]"></div>
                    </div>
                </div>
            </section>
            </x-webweb.container>
</x-layouts.guest>
