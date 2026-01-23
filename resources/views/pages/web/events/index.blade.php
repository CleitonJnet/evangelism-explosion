@php
    $title = __('Eventos & treinamentos');
    $description =
        'Confira ou agende eventos e treinamentos do ministério de Evangelismo Explosivo no Brasil, participando da expansão do Evangelho.';
    $keywords = 'agenda, eventos, treinamentos, evangelismo, EE Brasil';
    $ogImage = asset('images/leadership-meeting.webp');
@endphp

<x-layouts.guest :title="$title" :description="$description" :keywords="$keywords" :ogImage="$ogImage">

    {{-- FUNDO MAIS CLARO (papel + brilho dourado discreto) --}}
    <div class="relative min-h-screen overflow-hidden bg-linear-to-b from-slate-200 via-slate-50 to-slate-400">

        {{-- Brilhos decorativos (não interferem no layout) --}}
        <div aria-hidden="true" class="pointer-events-none absolute -top-24 -left-24 h-130 w-130 rounded-full blur-3xl"
            style="background: radial-gradient(circle at 30% 30%, rgba(199,168,64,.22), transparent 60%);">
        </div>

        <div aria-hidden="true" class="pointer-events-none absolute -top-28 -right-24 h-130 w-130 rounded-full blur-3xl"
            style="background: radial-gradient(circle at 70% 20%, rgba(138,116,36,.18), transparent 62%);">
        </div>

        {{-- Header já pronto --}}
        <x-web.header :title="$title" subtitle="Lista de eventos e treinamentos disponíveis" :cover="asset('images/clinic-ee.webp')" />

        <div class="p-4 max-w-8xl mx-auto sm:px-6 lg:px-8 md:py-10">

            <div class="relative">

                {{-- Seção: Jovens e Adultos --}}
                <div class="relative px-4 py-10 bg-center bg-no-repeat bg-cover max-w-8xl mx-auto sm:px-6 lg:px-10 rounded-2xl z-0 overflow-hidden shadow-md
           before:absolute before:inset-0 before:z-0
           before:bg-linear-to-b before:from-sky-800/70 before:via-black/70 before:to-sky-950/95
           before:backdrop-blur-0.5"
                    style="background-image: url({{ asset('images/clinic-ee.webp') }})">

                    <h3 class="relative z-10 max-w-xl mb-8 text-lg text-white sm:text-xl md:text-2xl"
                        style="font-family: 'Cinzel', serif;">
                        Treinamento para implementação do <span class="font-semibold text-amber-300">Evangelismo
                            Eficaz</span>
                        <span
                            class="absolute left-0 -bottom-2 h-0.5 w-3/5
                       bg-linear-to-r from-[#b79d46] via-[#c7a84099] to-[#8a742455] opacity-90">
                        </span>

                    </h3>

                    {{-- Lista de treinamentos do Evangelismo Eficaz --}}
                    {{-- <x-src.carousel /> --}}
                    <x-src.carousel :ministry="1" />

                </div>

                {{-- Linha temática --}}
                <div class="my-4 h-0.5 w-11/12 mx-auto"
                    style="border-radius: 100%; background: linear-gradient(135deg, rgba(199,168,64,.18), rgba(199,168,64,.55), rgba(199,168,64,.18));">
                </div>

                {{-- Seção: Crianças --}}
                <div class="relative px-4 py-10 bg-center bg-no-repeat bg-cover max-w-8xl mx-auto sm:px-6 lg:px-10 rounded-2xl"
                    style="background-image: url({{ asset('images/ee-kids/HFK-Graphic-blue-boat.png') }})">

                    <h3 class="text-2xl font-extrabold text-white md:text-4xl">
                        Workshop
                    </h3>
                    <img src="{{ asset('images/logo/hope-for-kids.webp') }}" alt="Logo Esperança Para Crianças"
                        class="object-contain w-full max-w-lg px-2 mb-4 -mt-4 md:-mt-6"
                        style="filter: drop-shadow(0 0 4px #fff)">

                    {{-- Lista de treinamentos --}}
                    <x-src.carousel :ministry="2" />
                </div>

                {{-- Linha temática --}}
                <div class="my-4 h-0.5 w-11/12 mx-auto"
                    style="border-radius: 100%; background: linear-gradient(135deg, rgba(199,168,64,.18), rgba(199,168,64,.55), rgba(199,168,64,.18));">
                </div>

                {{-- Seção: Outros Eventos --}}
                <div class="relative px-4 py-10 bg-center bg-no-repeat bg-cover max-w-8xl mx-auto sm:px-6 lg:px-10 rounded-2xl z-0 overflow-hidden shadow-md
           before:absolute before:inset-0 before:z-0
           before:bg-linear-to-b before:from-sky-800/70 before:via-black/70 before:to-sky-950/95
           before:backdrop-blur-0.5"
                    style="background-image: url({{ asset('images/church-clinic-base.webp') }})">

                    <h3 class="relative z-10 mb-8 text-lg text-white sm:text-xl md:text-2xl"
                        style="font-family: 'Cinzel', serif;">
                        Outros Eventos
                        {{-- Linha temática (dourado metálico) --}}
                        <span
                            class="absolute left-0 -bottom-2 h-0.5 w-3/5
                       bg-linear-to-r from-[#b79d46] via-[#c7a84099] to-[#8a742455] opacity-90">
                        </span>
                    </h3>

                    {{-- Lista de treinamentos --}}
                    {{-- @livewire('web.event.index', ['category' => 'evangelismo explosivo']) --}}
                    <x-src.carousel :ministry-not="[1, 2]" />

                </div>

            </div>

            {{-- CTA: Igreja Base --}}
            <div
                class="mt-10 bg-white overflow-hidden rounded-3xl border border-amber-400/25 ring-1 ring-black/5 shadow-[0_18px_45px_-30px_rgba(2,6,23,.35)] group">
                <div class="p-6 sm:p-8">
                    <div class="flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">

                        <div class="max-w-3xl">
                            <h4 class="text-2xl sm:text-3xl text-slate-900" style="font-family: 'Cinzel', serif;">
                                Multiplique e torne-se uma <span class="text-amber-700">Igreja Base de
                                    Treinamentos</span>
                            </h4>

                            <p class="mt-3 leading-relaxed text-slate-700">
                                O Evangelismo Explosivo se multiplica por meio de igrejas locais.
                                Ao tornar-se uma <em>Igreja <strong>Base de Treinamentos</strong></em>, sua igreja
                                não apenas capacita outras congregações,
                                mas experimenta <em>crescimento espiritual</em> ao formar discípulos que
                                <strong>aprendem a evangelizar e a mentorear</strong> outros.
                            </p>

                            <div class="flex flex-col gap-3 mt-6 sm:flex-row sm:items-center">

                                <x-src.btn-gold label="Quero ser uma Igreja Base de Treinamentos" :route="route('web.event.clinic-base')">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="w-5 h-5 ml-1.5 -translate-x-1 group-hover:opacity-100 group-hover:translate-x-0 group-hover:animate-[arrow-pulse_800ms_ease-in-out_infinite]"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        style="filter: drop-shadow(0 1px 1px #fff)">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </x-src.btn-gold>

                            </div>
                        </div>

                        <div class="w-full max-w-md">
                            <div
                                class="relative p-6 overflow-hidden border rounded-2xl bg-slate-100/70 ring-1 ring-slate-900/10 border-amber-300/25">
                                <div
                                    class="absolute inset-x-0 top-0 h-1 bg-linear-to-r from-[#8a7424] via-[#c7a840] to-[#f1d57a]">
                                </div>

                                <p class="text-sm font-semibold text-amber-800">
                                    ATÉ QUE TODOS OUÇAM!
                                </p>

                                <ul class="mt-4 space-y-3 text-sm text-slate-700">
                                    <li class="flex gap-2">
                                        <span class="mt-1 w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Evangelismo como estilo de vida
                                    </li>
                                    <li class="flex gap-2">
                                        <span class="mt-1 w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Discipulado que gera novos mentores
                                    </li>
                                    <li class="flex gap-2">
                                        <span class="mt-1 w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Ministério contínuo (crescimento saudável)
                                    </li>
                                </ul>
                            </div>
                        </div>

                    </div>
                </div>
            </div>


        </div>
    </div>

    {{-- Pequena animação reutilizada (seta) --}}
    @push('css')
        <style>
            @keyframes arrow-pulse {

                0%,
                100% {
                    transform: translateX(0);
                }

                50% {
                    transform: translateX(4px);
                }
            }
        </style>
    @endpush

</x-layouts.guest>
