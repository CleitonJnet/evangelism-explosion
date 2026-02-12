<section id="events"
    class="relative z-0 overflow-hidden bg-center bg-fixed bg-cover shadow-md
           before:absolute before:inset-0 before:z-0
           before:bg-linear-to-b before:from-sky-950 before:via-sky-800/70 before:to-sky-950/95
           before:backdrop-blur-[2px]"
    style="background-image: url({{ asset('images/ee-kids/training-hope-for-kids-workshop.webp') }});">

    {{-- brilho sutil dourado (bem discreto) --}}
    <div
        class="absolute inset-0 pointer-events-none opacity-60 bg-[radial-gradient(circle_at_20%_20%,rgba(199,168,64,0.18),transparent_55%)]">
    </div>

    <div class="relative px-4 py-10 mx-auto max-w-8xl mx-auto sm:px-6 lg:px-10">

        {{-- Cabeçalho do bloco --}}
        <div class="flex flex-col items-start justify-between gap-6 md:flex-row md:items-center">
            <div class="flex items-center gap-4 text-white">
                <div>
                    <h3 class="text-3xl sm:text-4xl font-averia-bold">
                        Próximos Treinamentos
                    </h3>
                    <p class="mt-1">
                        Treinamentos e capacitações para fortalecer evangelismo e discipulado na igreja local.
                    </p>
                </div>
            </div>

            <a href="{{ route('web.event.index') }}"
                class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold transition border shadow-md group rounded-xl border-amber-400/40 bg-black/25 shine text-amber-100 shadow-black/30 ring-1 ring-white/10 hover:bg-black/35 hover:border-amber-300/60 hover:text-amber-50">
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
            <x-src.carousel :ministry="2" />
        </div>

        <div class="relative overflow-hidden border shadow-xl reveal rounded-3xl border-slate-200">
            <div class="relative p-8 lg:p-10 bg-white/90">
                <div class="flex flex-col items-start justify-between gap-8 lg:flex-row lg:items-center">
                    <div class="max-w-2xl">
                        <h2 class="text-2xl font-averia-light sm:text-3xl text-slate-900">
                            Sua igreja já recebeu a capacitação?
                        </h2>
                        <p class="mt-4 text-lg leading-relaxed text-slate-600">
                            Se sua igreja já participou do Workshop/Clínica, nós ajudamos você a solicitar
                            <strong>materiais de implementação</strong> e orientar os próximos passos para o ciclo
                            do EPC.
                        </p>

                        <div class="grid gap-3 mt-6 sm:grid-cols-3">
                            <div class="p-4 bg-white border rounded-2xl border-slate-200">
                                <p class="text-xs font-semibold text-slate-500">1</p>
                                <p class="mt-1 font-extrabold text-slate-900">Confirme líderes-chave</p>
                                <p class="mt-1 text-sm text-slate-600">Quem vai conduzir?</p>
                            </div>
                            <div class="p-4 bg-white border rounded-2xl border-slate-200">
                                <p class="text-xs font-semibold text-slate-500">2</p>
                                <p class="mt-1 font-extrabold text-slate-900">Solicite os kits</p>
                                <p class="mt-1 text-sm text-slate-600">Materiais e orientações</p>
                            </div>
                            <div class="p-4 bg-white border rounded-2xl border-slate-200">
                                <p class="text-xs font-semibold text-slate-500">3</p>
                                <p class="mt-1 font-extrabold text-slate-900">Implemente o ciclo</p>
                                <p class="mt-1 text-sm text-slate-600">Com acompanhamento</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col w-full gap-3 lg:w-auto">
                        <button type="button" data-open-wa
                            class="inline-flex items-center justify-center px-6 py-3 font-black text-white transition bg-green-600 shine rounded-xl hover:bg-green-700">
                            Solicitar pelo WhatsApp
                        </button>
                        <a href="mailto:eebrasil@eebrasil.org.br"
                            class="inline-flex items-center justify-center px-6 py-3 font-black text-white transition rounded-xl bg-slate-900 shine hover:bg-slate-800">
                            Solicitar por e-mail
                        </a>
                        <a href="{{ route('web.event.index') }}"
                            class="inline-flex items-center justify-center px-6 py-3 font-black transition rounded-xl bg-amber-400 shine text-slate-950 hover:brightness-95">
                            Preciso do Workshop primeiro
                        </a>
                    </div>
                </div>

                <div class="p-6 mt-8 text-white rounded-2xl bg-sky-950">
                    <p class="text-xl font-averia-light">Dica prática</p>
                    <p class="mt-2 text-white/85">
                        <strong>Centralize o planejamento do EPC com 1 a 3 líderes-chave:</strong> calendário, equipe,
                        salas, materiais, comunicação com pais e acompanhamento do progresso das crianças.
                    </p>
                </div>
            </div>
        </div>

    </div>
</section>
