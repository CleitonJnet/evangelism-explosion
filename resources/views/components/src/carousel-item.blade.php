@props([
    'category' => null,
    'type' => null,
    'event' => null,
    'date' => null,
    'start_time' => null,
    'city' => null,
    'state' => null,
    'route' => '#',
])

<div class="overflow-hidden swiper-slide shine rounded-2xl"
    style="background: linear-gradient(180deg, #082f49 0%, #05273d 55%, #041b2d 100%);">
    <a href="{{ $route }}" class="block h-full group">

        <div
            class="relative h-full overflow-hidden transition border shadow-lg rounded-2xl border-amber-300/20 bg-white/5 backdrop-blur-md ring-1 ring-white/10 shadow-black/30 hover:border-amber-300/40 hover:bg-white/10 hover:backdrop-blur-xl">

            <div class="absolute inset-x-0 top-0 h-0.75 bg-linear-to-r from-[#8a7424] via-[#c7a840] to-[#f1d57a]">
            </div>

            <div class="p-3 pt-5">

                <div class="px-2 py-1 mb-2 text-sm truncate border rounded-lg bg-black/20 text-amber-100/90 border-amber-400/20"
                    title="{{ $category }}">
                    {{ $category }}
                </div>

                <div class="p-4 text-center shadow-sm rounded-xl bg-white/85 backdrop-blur-md text-slate-900">

                    <div class="flex justify-center py-3 text-left pt-serif-regular"
                        title="{{ $type }}: {{ $event }}">
                        <div class="leading-5">
                            <span class="text-lg"
                                style="color: #000000';
                                                text-shadow: 1px 1px 1px rgba(0,0,0,0.5);">
                                {{ $type }}
                            </span>
                            <br>
                            <div class="font-bold uppercase truncate" style="font-size: clamp(16px, 1.21vw, 18px)">
                                {{ $event }}
                            </div>
                        </div>
                    </div>

                    <div class="px-2 mt-2 text-sm truncate">
                        <span class="opacity-80" style="color: #000000;"
                            title="{{ $date }} às {{ $start_time }}">
                            Início do Evento
                        </span>
                    </div>

                    <div class="px-2 text-lg font-semibold" title="{{ $date }}">
                        {{ $date }}
                        <span class="py-1 text-base font-light">
                            a partir das <span class="font-bold">{{ $start_time }}</span>
                        </span>
                    </div>

                    <div class="py-2 mt-2 text-sm font-bold leading-5 truncate text-nowrap"
                        title="{{ $city }}, {{ $state }}">
                        {{ $city }}, {{ $state }}
                    </div>

                </div>

                <div class="mt-2 flex items-center justify-center gap-1.5 font-bold text-amber-200"
                    style="text-shadow: 1px 1px 1px black">
                    Saiba mais.
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="w-4 h-4 opacity-0 -translate-x-1 group-hover:opacity-100 group-hover:translate-x-0
                                        group-hover:animate-[arrow-pulse_800ms_ease-in-out_infinite]"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        style="filter: drop-shadow(1px 1px 1px #000000ab)">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </div>

                <div aria-hidden="true"
                    class="absolute inset-x-0 bottom-0 h-1 pointer-events-none bg-linear-to-r from-transparent via-amber-500 to-transparent">
                </div>

            </div>
        </div>
    </a>
</div>
