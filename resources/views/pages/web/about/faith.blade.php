@php
    // Metadados para a página de declaração de fé
    $title = 'Declaração de Fé';
    $description =
        'Confira a declaração de fé do Evangelismo Explosivo: nossas convicções sobre a Bíblia, Deus, Cristo, a salvação e a missão da igreja.';
    $keywords = 'declaração de fé evangelismo explosivo, doutrina, crenças, fé cristã, Bíblia';
    $ogImage = asset('images/og/faith.webp');
@endphp

<x-layouts.guest :title="$title" :description="$description" :keywords="$keywords" :ogImage="$ogImage" class="pb-10">
    <x-web.header :title="$title" subtitle='Em que acreditamos como ministério' :cover="asset('images/3rd_nations_congress_2016.webp')" />

    {{-- Conteúdo principal em cards com grade responsiva --}}
    <x-web.container class="mt-10">
        <p class="max-w-3xl mx-auto mb-8 leading-relaxed 2md:text-center text-[#574815] font-bold text-lg">
            As bases doutrinárias do Evangelismo Explosivo Internacional refletem uma declaração evangélica ampla e fiel
            às Escrituras. Não buscamos impor novas doutrinas às igrejas ou denominações, mas caminhar em unidade,
            afirmando juntos as verdades básicas do Evangelho.
        </p>

        <div
            class="relative flex flex-wrap gap-4 md:gap-6 lg:gap-8 pt-10 before:absolute before:top-0 before:w-full before:bg-linear-to-r before:from-transparent before:via-amber-500/15 before:to-transparent before:left-0 before:h-0.5">
            {{-- A Escritura --}}
            <div
                class="basis-44 flex-auto flex flex-col overflow-hidden border shadow-md bg-white/95 border-amber-500/25 shadow-black/10 rounded-2xl">
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
                class="basis-56 flex-auto flex flex-col overflow-hidden border shadow-md bg-white/95 border-amber-500/25 shadow-black/10 rounded-2xl">
                <div class="flex flex-col flex-1 p-4">
                    <h3 class="mb-2 text-lg font-semibold text-[#8a7424]" style="font-family: 'Cinzel', serif;">O
                        Deus Triúno</h3>
                    <p class="flex-1 leading-relaxed text-slate-600">Há um só Deus, o qual existe eternamente em três
                        pessoas: Pai, Filho e Espírito Santo.</p>
                </div>
                <div class="h-1 w-full bg-linear-to-r from-[#f1d57a] via-[#c7a840] to-[#8a7424]"></div>
            </div>
            {{-- Jesus Cristo --}}
            <div
                class="basis-120 flex-auto flex flex-col overflow-hidden border shadow-md bg-white/95 border-amber-500/25 shadow-black/10 rounded-2xl">
                <div class="flex flex-col flex-1 p-4">
                    <h3 class="mb-2 text-lg font-semibold text-[#8a7424]" style="font-family: 'Cinzel', serif;">Jesus
                        Cristo</h3>
                    <p class="flex-1 leading-relaxed text-slate-600">É o Deus Homem em uma só pessoa. Ele nasceu de uma
                        virgem. Viveu vida sem pecado, operou milagres, vicariamente expiou nossos pecados através de
                        seu sangue e morte. Ele ressuscitou dos mortos e assentou-se à direita de Deus pai e, voltará em
                        poder e glória.</p>
                </div>
                <div class="h-1 w-full bg-linear-to-r from-[#f1d57a] via-[#c7a840] to-[#8a7424]"></div>
            </div>
            {{-- Regeneração --}}
            <div
                class="basis-60 flex-auto flex flex-col overflow-hidden border shadow-md bg-white/95 border-amber-500/25 shadow-black/10 rounded-2xl">
                <div class="flex flex-col flex-1 p-4">
                    <h3 class="mb-2 text-lg font-semibold text-[#8a7424]" style="font-family: 'Cinzel', serif;">
                        Regeneração</h3>
                    <p class="flex-1 leading-relaxed text-slate-600">A regeneração efetuada pelo Espírito Santo é
                        absolutamente necessária para a salvação dos pecadores perdidos.</p>
                </div>
                <div class="h-1 w-full bg-linear-to-r from-[#f1d57a] via-[#c7a840] to-[#8a7424]"></div>
            </div>
            {{-- Salvação pela fé --}}
            <div
                class="basis-60 flex-auto flex flex-col overflow-hidden border shadow-md bg-white/95 border-amber-500/25 shadow-black/10 rounded-2xl">
                <div class="flex flex-col flex-1 p-4">
                    <h3 class="mb-2 text-lg font-semibold text-[#8a7424]" style="font-family: 'Cinzel', serif;">Salvação
                        pela fé</h3>
                    <p class="flex-1 leading-relaxed text-slate-600">A vida eterna é recebida pela fé,
                        o que significa confiar somente em Jesus para a salvação.</p>
                </div>
                <div class="h-1 w-full bg-linear-to-r from-[#f1d57a] via-[#c7a840] to-[#8a7424]"></div>
            </div>
            {{-- O Espírito Santo --}}
            <div
                class="basis-72 flex-auto flex flex-col overflow-hidden border shadow-md bg-white/95 border-amber-500/25 shadow-black/10 rounded-2xl">
                <div class="flex flex-col flex-1 p-4">
                    <h3 class="mb-2 text-lg font-semibold text-[#8a7424]" style="font-family: 'Cinzel', serif;">O
                        Espírito Santo</h3>
                    <p class="flex-1 leading-relaxed text-slate-600">O Espírito Santo habita em todo verdadeiro crente,
                        capacitando-o a viver uma vida que agrada a Deus.</p>
                </div>
                <div class="h-1 w-full bg-linear-to-r from-[#f1d57a] via-[#c7a840] to-[#8a7424]"></div>
            </div>
            {{-- Ressurreição --}}
            <div
                class="basis-96 flex-auto flex flex-col overflow-hidden border shadow-md bg-white/95 border-amber-500/25 shadow-black/10 rounded-2xl">
                <div class="flex flex-col flex-1 p-4">
                    <h3 class="mb-2 text-lg font-semibold text-[#8a7424]" style="font-family: 'Cinzel', serif;">
                        Ressurreição</h3>
                    <p class="flex-1 leading-relaxed text-slate-600">Tanto os salvos como os perdidos ressuscitarão dos
                        mortos: os salvos para Vida Eterna e os perdidos "para a vergonha e o horror do eterno".</p>
                </div>
                <div class="h-1 w-full bg-linear-to-r from-[#f1d57a] via-[#c7a840] to-[#8a7424]"></div>
            </div>
            {{-- Unidade --}}
            <div
                class="basis-56 flex-auto flex flex-col overflow-hidden border shadow-md bg-white/95 border-amber-500/25 shadow-black/10 rounded-2xl">
                <div class="flex flex-col flex-1 p-4">
                    <h3 class="mb-2 text-lg font-semibold text-[#8a7424]" style="font-family: 'Cinzel', serif;">Unidade
                    </h3>
                    <p class="flex-1 leading-relaxed text-slate-600">Todos os verdadeiros crentes formam uma
                        unidade perfeita em Jesus Cristo, o Senhor.</p>
                </div>
                <div class="h-1 w-full bg-linear-to-r from-[#f1d57a] via-[#c7a840] to-[#8a7424]"></div>
            </div>
            {{-- Igreja local --}}
            <div
                class="basis-60 flex-auto flex flex-col overflow-hidden border shadow-md bg-white/95 border-amber-500/25 shadow-black/10 rounded-2xl">
                <div class="flex flex-col flex-1 p-4">
                    <h3 class="mb-2 text-lg font-semibold text-[#8a7424]" style="font-family: 'Cinzel', serif;">Igreja
                        local</h3>
                    <p class="flex-1 leading-relaxed text-slate-600">A igreja local é a primeira base de operação de
                        Deus estabelecida para atividade evangelizadora do mundo.</p>
                </div>
                <div class="h-1 w-full bg-linear-to-r from-[#f1d57a] via-[#c7a840] to-[#8a7424]"></div>
            </div>
            {{-- Comissão --}}
            <div
                class="basis-96 flex-auto flex flex-col overflow-hidden border shadow-md bg-white/95 border-amber-500/25 shadow-black/10 rounded-2xl">
                <div class="flex flex-col flex-1 p-4">
                    <h3 class="mb-2 text-lg font-semibold text-[#8a7424]" style="font-family: 'Cinzel', serif;">Comissão
                    </h3>
                    <p class="flex-1 leading-relaxed text-slate-600">Cada crente genuíno é comissionado por Cristo para
                        "pregar o evangelho a toda criatura" e, "fazer discípulos de todas as nações".</p>
                </div>
                <div class="h-1 w-full bg-linear-to-r from-[#f1d57a] via-[#c7a840] to-[#8a7424]"></div>
            </div>
            {{-- Treinamento prático --}}
            <div
                class="basis-120 flex-auto flex flex-col overflow-hidden border shadow-md bg-white/95 border-amber-500/25 shadow-black/10 rounded-2xl">
                <div class="flex flex-col flex-1 p-4">
                    <h3 class="mb-2 text-lg font-semibold text-[#8a7424]" style="font-family: 'Cinzel', serif;">
                        Treinamento
                        prático</h3>
                    <p class="flex-1 leading-relaxed text-slate-600">Os crentes estarão melhor capacitados a
                        compartilhar o evangelho se participarem de treinamento realizado em situação prática, através
                        de discipulado em grupo, dirigido por treinadores experientes.</p>
                </div>
                <div class="h-1 w-full bg-linear-to-r from-[#f1d57a] via-[#c7a840] to-[#8a7424]"></div>
            </div>
            {{-- Criação e família --}}
            {{-- <div
                class="basis-96 flex-auto flex flex-col overflow-hidden border shadow-md bg-white/95 border-amber-500/25 shadow-black/10 rounded-2xl">
                <div class="flex flex-col flex-1 p-4">
                    <h3 class="mb-2 text-lg font-semibold text-[#8a7424]" style="font-family: 'Cinzel', serif;">
                        Criação e
                        família</h3>
                    <p class="flex-1 leading-relaxed text-slate-600">Deus criou homem e mulher à Sua imagem para
                        complementarem‑se, e o casamento é a união entre um homem e uma mulher.</p>
                </div>
                <div class="h-1 w-full bg-linear-to-r from-[#f1d57a] via-[#c7a840] to-[#8a7424]"></div>
            </div> --}}
        </div>

        <p class="mt-8 leading-relaxed text-[#574815] max-w-5xl mx-auto text-center">
            Esta declaração reflete a convicção de que o Evangelho deve ser proclamado em unidade,
            sem substituir as convicções denominacionais, e que cada crente pode se unir na simplicidade
            e profundidade da mensagem de Jesus.
        </p>
    </x-web.container>
</x-layouts.guest>
