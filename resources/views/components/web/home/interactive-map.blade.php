<section class="px-4 pb-6 mx-auto max-w-8xl mx-auto sm:px-6 lg:px-8 group">
    <div class="p-6 bg-white border rounded-lg shadow-md border-amber-500/20 shadow-black/10">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h3 class="text-xl text-[#1b1709] leading-snug" style="font-family: 'Cinzel', sans-serif;">
                Veja como ser uma igreja <div class="font-semibold">BASE DE CLÍNICAS</div>
            </h3>

            <a href="{{ route('web.event.clinic-base') }}"
                class="inline-flex items-center gap-2 text-sm font-semibold
                      text-[#8a7424] hover:text-[#c7a840] transition
                      focus:outline-none focus:ring-2 focus:ring-amber-400/40 rounded-md">
                Mais informações

                <svg xmlns="http://www.w3.org/2000/svg"
                    class="w-4 h-4 opacity-0 -translate-x-1 transition-all duration-300 ease-out
                            group-hover:opacity-100 group-hover:translate-x-0
                            group-hover:animate-[arrow-pulse_1s_ease-in-out_infinite]"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </a>
        </div>


        <div class="w-full gap-16 mt-4 lg:grid lg:grid-cols-2 lg:gap-24">
            <div>
                @livewire(name: 'web.home.mapa') {{-- mapa interativo --}}
            </div>

            <div class="w-full">
                @livewire(name: 'web.home.base-clinics') {{-- Igrejas base de clínicas --}}
            </div>

        </div>
    </div>
</section>
