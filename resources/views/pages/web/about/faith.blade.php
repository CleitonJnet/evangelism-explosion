@php
    // Metadados para a página de declaração de fé
    $title = 'Declaração de Fé';
    $description =
        'Confira a declaração de fé do Evangelismo Explosivo: nossas convicções sobre a Bíblia, Deus, Cristo, a salvação e a missão da igreja.';
    $keywords = 'declaração de fé evangelismo explosivo, doutrina, crenças, fé cristã, Bíblia';
    $ogImage = asset('images/og/faith.webp');
@endphp

<x-layouts.guest :title="$title" :description="$description" :keywords="$keywords" :ogImage="$ogImage">
    <x-web.header :title="$title" subtitle='Em que acreditamos como ministério' :cover="asset('images/3rd_nations_congress_2016.webp')" />

    {{-- Conteúdo principal em cards com grade responsiva --}}
    <x-web.container>
        <p class="max-w-3xl mx-auto mb-8 leading-relaxed 2md:text-center text-[#574815]">
            As bases doutrinárias do Evangelismo Explosivo Internacional refletem uma declaração evangélica ampla e fiel
            às Escrituras. Não buscamos impor novas doutrinas às igrejas ou denominações, mas caminhar em unidade,
            afirmando juntos as verdades básicas do Evangelho.
        </p>

        <div
            class="relative grid grid-cols-1 md:gap-6 lg:gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 pt-10 before:absolute before:top-0 before:w-full before:bg-linear-to-r before:from-transparent before:via-amber-500/15 before:to-transparent before:left-0 before:h-0.5"">
            {{-- A Escritura --}}
            <div
                class="flex flex-col overflow-hidden border shadow-md bg-white/95 border-amber-500/25 shadow-black/10 rounded-2xl">
                <div class="flex flex-col flex-1 p-4">
                    <h3 class="mb-2 text-lg font-semibold text-[#8a7424]" style="font-family: 'Cinzel', serif;">A
                        Escritura</h3>
                    <p class="flex-1 leading-relaxed text-slate-600">A Bíblia é a Palavra de Deus, inspirada e
                        infalível.</p>
                </div>
                <div class="h-1 w-full bg-linear-to-r from-[#f1d57a] via-[#c7a840] to-[#8a7424]">
                </div>
            </div>
            {{-- O Deus Triúno --}}
            <div
                class="flex flex-col overflow-hidden border shadow-md bg-white/95 border-amber-500/25 shadow-black/10 rounded-2xl">
                <div class="flex flex-col flex-1 p-4">
                    <h3 class="mb-2 text-lg font-semibold text-[#8a7424]" style="font-family: 'Cinzel', serif;">O
                        Deus Triúno</h3>
                    <p class="flex-1 leading-relaxed text-slate-600">Há um único Deus, eternamente existente em três
                        Pessoas: Pai, Filho e Espírito Santo.</p>
                </div>
                <div class="h-1 w-full bg-linear-to-r from-[#f1d57a] via-[#c7a840] to-[#8a7424]"></div>
            </div>
            {{-- Jesus Cristo --}}
            <div
                class="flex flex-col overflow-hidden border shadow-md bg-white/95 border-amber-500/25 shadow-black/10 rounded-2xl">
                <div class="flex flex-col flex-1 p-4">
                    <h3 class="mb-2 text-lg font-semibold text-[#8a7424]" style="font-family: 'Cinzel', serif;">Jesus
                        Cristo</h3>
                    <p class="flex-1 leading-relaxed text-slate-600">Ele é plenamente Deus e plenamente homem; nasceu
                        de uma virgem, viveu sem pecado, morreu vicariamente pelos pecados, ressuscitou corporalmente,
                        ascendeu aos céus e voltará em glória.</p>
                </div>
                <div class="h-1 w-full bg-linear-to-r from-[#f1d57a] via-[#c7a840] to-[#8a7424]"></div>
            </div>
            {{-- Regeneração --}}
            <div
                class="flex flex-col overflow-hidden border shadow-md bg-white/95 border-amber-500/25 shadow-black/10 rounded-2xl">
                <div class="flex flex-col flex-1 p-4">
                    <h3 class="mb-2 text-lg font-semibold text-[#8a7424]" style="font-family: 'Cinzel', serif;">
                        Regeneração</h3>
                    <p class="flex-1 leading-relaxed text-slate-600">A regeneração pelo Espírito Santo é absolutamente
                        necessária para a salvação.</p>
                </div>
                <div class="h-1 w-full bg-linear-to-r from-[#f1d57a] via-[#c7a840] to-[#8a7424]"></div>
            </div>
            {{-- Salvação pela fé --}}
            <div
                class="flex flex-col overflow-hidden border shadow-md bg-white/95 border-amber-500/25 shadow-black/10 rounded-2xl">
                <div class="flex flex-col flex-1 p-4">
                    <h3 class="mb-2 text-lg font-semibold text-[#8a7424]" style="font-family: 'Cinzel', serif;">Salvação
                        pela fé</h3>
                    <p class="flex-1 leading-relaxed text-slate-600">A vida eterna é recebida somente pela fé,
                        confiando exclusivamente em Jesus Cristo para a salvação.</p>
                </div>
                <div class="h-1 w-full bg-linear-to-r from-[#f1d57a] via-[#c7a840] to-[#8a7424]"></div>
            </div>
            {{-- O Espírito Santo --}}
            <div
                class="flex flex-col overflow-hidden border shadow-md bg-white/95 border-amber-500/25 shadow-black/10 rounded-2xl">
                <div class="flex flex-col flex-1 p-4">
                    <h3 class="mb-2 text-lg font-semibold text-[#8a7424]" style="font-family: 'Cinzel', serif;">O
                        Espírito Santo</h3>
                    <p class="flex-1 leading-relaxed text-slate-600">Ele habita em todo verdadeiro crente,
                        capacitando‑o a viver uma vida santa.</p>
                </div>
                <div class="h-1 w-full bg-linear-to-r from-[#f1d57a] via-[#c7a840] to-[#8a7424]"></div>
            </div>
            {{-- Ressurreição --}}
            <div
                class="flex flex-col overflow-hidden border shadow-md bg-white/95 border-amber-500/25 shadow-black/10 rounded-2xl">
                <div class="flex flex-col flex-1 p-4">
                    <h3 class="mb-2 text-lg font-semibold text-[#8a7424]" style="font-family: 'Cinzel', serif;">
                        Ressurreição</h3>
                    <p class="flex-1 leading-relaxed text-slate-600">Haverá ressurreição tanto dos salvos quanto dos
                        perdidos: uns para a vida eterna, outros para a condenação.</p>
                </div>
                <div class="h-1 w-full bg-linear-to-r from-[#f1d57a] via-[#c7a840] to-[#8a7424]"></div>
            </div>
            {{-- Unidade --}}
            <div
                class="flex flex-col overflow-hidden border shadow-md bg-white/95 border-amber-500/25 shadow-black/10 rounded-2xl">
                <div class="flex flex-col flex-1 p-4">
                    <h3 class="mb-2 text-lg font-semibold text-[#8a7424]" style="font-family: 'Cinzel', serif;">Unidade
                    </h3>
                    <p class="flex-1 leading-relaxed text-slate-600">Todos os verdadeiros crentes formam uma
                        unidade espiritual em Cristo.</p>
                </div>
                <div class="h-1 w-full bg-linear-to-r from-[#f1d57a] via-[#c7a840] to-[#8a7424]"></div>
            </div>
            {{-- Igreja local --}}
            <div
                class="flex flex-col overflow-hidden border shadow-md bg-white/95 border-amber-500/25 shadow-black/10 rounded-2xl">
                <div class="flex flex-col flex-1 p-4">
                    <h3 class="mb-2 text-lg font-semibold text-[#8a7424]" style="font-family: 'Cinzel', serif;">Igreja
                        local</h3>
                    <p class="flex-1 leading-relaxed text-slate-600">A igreja local é a base principal estabelecida
                        por Deus para a evangelização do mundo.</p>
                </div>
                <div class="h-1 w-full bg-linear-to-r from-[#f1d57a] via-[#c7a840] to-[#8a7424]"></div>
            </div>
            {{-- Comissão --}}
            <div
                class="flex flex-col overflow-hidden border shadow-md bg-white/95 border-amber-500/25 shadow-black/10 rounded-2xl">
                <div class="flex flex-col flex-1 p-4">
                    <h3 class="mb-2 text-lg font-semibold text-[#8a7424]" style="font-family: 'Cinzel', serif;">Comissão
                    </h3>
                    <p class="flex-1 leading-relaxed text-slate-600">Todo crente é comissionado por Cristo a
                        proclamar o Evangelho e fazer discípulos de todas as nações.</p>
                </div>
                <div class="h-1 w-full bg-linear-to-r from-[#f1d57a] via-[#c7a840] to-[#8a7424]"></div>
            </div>
            {{-- Treinamento prático --}}
            <div
                class="flex flex-col overflow-hidden border shadow-md bg-white/95 border-amber-500/25 shadow-black/10 rounded-2xl">
                <div class="flex flex-col flex-1 p-4">
                    <h3 class="mb-2 text-lg font-semibold text-[#8a7424]" style="font-family: 'Cinzel', serif;">
                        Treinamento
                        prático</h3>
                    <p class="flex-1 leading-relaxed text-slate-600">Os crentes são melhor capacitados a compartilhar
                        o Evangelho por meio de treinamento prático e discipulado em grupo sob liderança experiente.</p>
                </div>
                <div class="h-1 w-full bg-linear-to-r from-[#f1d57a] via-[#c7a840] to-[#8a7424]"></div>
            </div>
            {{-- Criação e família --}}
            <div
                class="flex flex-col overflow-hidden border shadow-md bg-white/95 border-amber-500/25 shadow-black/10 rounded-2xl">
                <div class="flex flex-col flex-1 p-4">
                    <h3 class="mb-2 text-lg font-semibold text-[#8a7424]" style="font-family: 'Cinzel', serif;">
                        Criação e
                        família</h3>
                    <p class="flex-1 leading-relaxed text-slate-600">Deus criou homem e mulher à Sua imagem para
                        complementarem‑se, e o casamento é a união entre um homem e uma mulher.</p>
                </div>
                <div class="h-1 w-full bg-linear-to-r from-[#f1d57a] via-[#c7a840] to-[#8a7424]"></div>
            </div>
        </div>

        <p class="mt-8 leading-relaxed text-[#574815]">
            Esta declaração reflete a convicção de que o Evangelho deve ser proclamado em unidade,
            sem substituir as convicções denominacionais, e que cada crente pode se unir na simplicidade
            e profundidade da mensagem de Jesus.
        </p>
        </x-webweb.container>
</x-layouts.guest>
