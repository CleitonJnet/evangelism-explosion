@props([
    'category' => null,
    'type' => null,
    'event' => null,
    'date' => null,
    'start_time' => null,
    'city' => null,
    'state' => null,
    'route' => '#',
    'banner' => false,
    'schedule' => false,
    'free' => false,
    'alert' => false,
    'alert_message' => null,
    'footer_label' => 'Saiba mais.',
])

<div class="overflow-hidden swiper-slide shine rounded-2xl max-w-sm"
    style="background: linear-gradient(180deg, #082f49 0%, #05273d 55%, #041b2d 100%);">
    <a href="{{ $route }}" class="block h-full group">

        <div
            class="relative h-full overflow-hidden transition border shadow-lg rounded-2xl border-amber-300/20 bg-white/5 backdrop-blur-md ring-1 ring-white/10 shadow-black/30 hover:border-amber-300/40 hover:bg-white/10 hover:backdrop-blur-xl">

            <div class="absolute inset-x-0 top-0 h-0.75 bg-linear-to-r from-[#8a7424] via-[#c7a840] to-[#f1d57a]">
            </div>

            @if ($alert)
                <div class="absolute right-3 top-3 z-10">
                    <div class="relative flex">
                        <span title="{{ $alert_message }}"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-red-700/80 bg-red-100/90 p-1.5 text-red-700 shadow-lg shadow-red-950/25 backdrop-blur-sm"
                            aria-label="{{ __('Alerta de evento') }}">
                            <svg version="1.0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 194 178"
                                preserveAspectRatio="xMidYMid meet" aria-hidden="true"
                                class="h-6 w-6 drop-shadow-[0_2px_4px_rgba(127,29,29,0.35)]" fill="currentColor">
                                <g transform="translate(0.000000,178.000000) scale(0.100000,-0.100000)" stroke="none">
                                    <path
                                        d="M825 1722 c-83 -41 -129 -94 -225 -262 -46 -80 -191 -331 -322 -559 -266 -459 -280 -493 -261 -600 31 -165 143 -273 302 -291 100 -12 1238 -11 1311 1 158 25 262 128 292 287 19 103 0 154 -165 439 -79 136 -224 388 -322 558 -101 177 -196 329 -219 353 -67 71 -124 95 -236 99 -89 4 -99 2 -155 -25z m255 -43 c97 -44 101 -49 268 -339 55 -96 190 -330 299 -520 110 -190 206 -362 213 -384 8 -24 11 -67 8 -111 -6 -86 -34 -143 -99 -199 -80 -68 -59 -67 -815 -64 l-679 3 -49 25 c-61 31 -120 97 -140 157 -19 55 -21 146 -4 194 15 43 630 1112 667 1159 16 19 56 50 90 68 53 27 73 32 129 32 46 0 81 -7 112 -21z" />
                                    <path
                                        d="M885 1561 c-16 -10 -37 -27 -46 -37 -14 -17 -155 -258 -541 -931 -55 -95 -104 -189 -109 -208 -19 -66 25 -156 92 -190 42 -22 1336 -22 1378 0 67 34 111 124 92 190 -5 19 -63 128 -129 242 -366 639 -508 880 -526 902 -45 51 -153 68 -211 32z m178 -388 c2 -5 -2 -109 -9 -233 -7 -124 -13 -240 -13 -257 l-1 -33 -68 0 -69 0 -6 113 c-4 61 -10 181 -13 265 l-7 152 91 0 c50 0 93 -3 95 -7z m-49 -623 c35 -13 59 -64 52 -106 -14 -70 -106 -99 -163 -50 -23 20 -28 32 -27 70 0 25 7 53 15 62 26 32 77 42 123 24z" />
                                </g>
                            </svg>
                        </span>
                        <div
                            class="pointer-events-none absolute right-0 top-10 hidden w-64 rounded-xl border border-red-200 bg-white px-3 py-2 text-xs font-semibold text-red-700 shadow-xl group-hover:block">
                            {{ $alert_message }}
                        </div>
                    </div>
                </div>
            @endif

            <div class="p-3 pt-5">

                <div class="px-2 py-1 mb-2 text-sm truncate border rounded-lg bg-black/20 text-amber-100/90 border-amber-400/20 flex justify-between"
                    title="{{ $category }}">
                    {{ $category }}
                    <div>
                        @if ($schedule)
                            <span>&#x2637;</span>
                        @endif
                        @if ($banner)
                            <span>&#x2750;</span>
                        @endif
                        @if ($free)
                            <span>&#x2666;</span>
                        @else
                            <span>&#x2662;</span>
                        @endif
                    </div>
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

                        @if ($start_time > 0)
                            <span class="py-1 text-base font-light">
                                a partir das <span class="font-bold">{{ $start_time }}</span>
                            </span>
                        @endif
                    </div>

                    <div class="py-2 mt-2 text-sm font-bold leading-5 truncate text-nowrap"
                        title="{{ $city }}, {{ $state }}">
                        {{ $city }}, {{ $state }}
                    </div>

                </div>

                <div class="mt-2 flex items-center justify-center gap-1.5 font-bold text-amber-200"
                    style="text-shadow: 1px 1px 1px black">
                    {{ $footer_label }}
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
