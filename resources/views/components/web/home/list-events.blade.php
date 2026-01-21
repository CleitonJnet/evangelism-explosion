@props(['ministry_id' => null])

<section id="events"
    class="relative z-0 overflow-hidden bg-center bg-cover shadow-md
           before:absolute before:inset-0 before:z-0
           before:bg-linear-to-b before:from-sky-800/70 before:via-black/70 before:to-sky-950/95
           before:backdrop-blur-[2px]"
    style="background-image: url({{ asset('images/leadership-meeting.webp') }});">

    {{-- brilho sutil dourado (bem discreto) --}}
    <div
        class="absolute inset-0 pointer-events-none opacity-60 bg-[radial-gradient(circle_at_20%_20%,rgba(199,168,64,0.18),transparent_55%)]">
    </div>

    <div class="relative px-4 py-10 mx-auto max-w-8xl mx-auto sm:px-6 lg:px-10">

        {{-- Cabeçalho do bloco --}}
        <div class="flex flex-col items-start justify-between gap-6 md:flex-row md:items-center">
            <div class="flex items-center gap-4">
                <div>
                    <h3 class="text-3xl text-white sm:text-4xl"
                        style="font-family: 'Cinzel', serif; text-shadow: 1px 1px 12px #000;">
                        Próximos eventos
                    </h3>
                    <p class="mt-1 text-sm text-white/75">
                        Treinamentos e capacitações para fortalecer evangelismo e discipulado na igreja local.
                    </p>
                </div>
            </div>

            <a href="{{ route('web.event.index') }}"
                class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold transition border shadow-md shine group rounded-xl border-amber-400/40 bg-black/25 text-amber-100 shadow-black/30 ring-1 ring-white/10 hover:bg-black/35 hover:border-amber-300/60 hover:text-amber-50">
                Todos os eventos

                <svg xmlns="http://www.w3.org/2000/svg"
                    class="w-4 h-4 opacity-0 -translate-x-1 group-hover:opacity-100 group-hover:translate-x-0
                           group-hover:animate-[arrow-pulse_1s_ease-in-out_infinite]"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    style="filter: drop-shadow(1px 1px 1px #000000ab)">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </a>
        </div>

        {{-- Lista de treinamentos --}}

        <div class="my-6 min-h-80">
            <x-src.carousel :ministry="$ministry_id" />
        </div>

        {{-- CTA: Igreja Base --}}
        <div
            class="mt-10 overflow-hidden border shadow-lg group rounded-2xl bg-white/5 backdrop-blur-md ring-1 ring-white/10 border-amber-400/25 shadow-black/30">
            <div class="p-6 sm:p-8">
                <div class="flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">
                    <div class="max-w-3xl">
                        <h4 class="text-2xl text-white sm:text-3xl" style="font-family: 'Cinzel', serif;">
                            Multiplique e torne-se uma <span class="text-amber-300">Igreja Base de Treinamentos</span>
                        </h4>

                        <p class="mt-3 leading-relaxed text-white/80">
                            O Evangelismo Explosivo se multiplica por meio de igrejas locais.
                            Ao tornar-se uma <em>Igreja <strong>Base de Treinamentos</strong></em>, sua igreja não
                            apenas capacita outras congregações, mas experimenta <em>crescimento espiritual</em> ao
                            formar discípulos que <strong>aprendem a evangelizar e a mentorear</strong> outros.
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

                    {{-- cartão lateral “selo” --}}
                    <div class="w-full max-w-md">
                        <div
                            class="relative p-6 overflow-hidden border rounded-2xl bg-black/30 border-amber-300/20 ring-1 ring-white/10">
                            <div
                                class="absolute inset-x-0 top-0 h-0.75 bg-linear-to-r from-[#8a7424] via-[#c7a840] to-[#f1d57a]">
                            </div>

                            <p class="text-sm font-semibold text-amber-200">ATÉ QUE TODOS OUÇAM!
                            </p>

                            <ul class="mt-4 space-y-3 text-sm text-white/75">
                                <li class="flex gap-2">
                                    <span class="mt-1 w-1.5 h-1.5 rounded-full bg-amber-300"></span>
                                    Evangelismo como estilo de vida
                                </li>
                                <li class="flex gap-2">
                                    <span class="mt-1 w-1.5 h-1.5 rounded-full bg-amber-300"></span>
                                    Discipulado que gera novos mentores
                                </li>
                                <li class="flex gap-2">
                                    <span class="mt-1 w-1.5 h-1.5 rounded-full bg-amber-300"></span>
                                    Ministério contínuo (crescimento saudável)
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>
