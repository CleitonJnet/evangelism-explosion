@props(['label', 'description' => 'Menu', 'width' => '340px', 'isroute' => false])

{{-- ======= DROPDOWN: CONTAINER ======= --}}
<div class="relative h-full" data-dropdown>
    {{-- Gatilho: ocupa toda a altura --}}
    <a data-dropdown-toggle
        class="flex items-center h-full gap-0.5 px-1 transition cursor-pointer {{ $isroute ? 'text-amber-300' : 'text-white/90' }} hover:text-amber-300">
        <span>{!! $label !!}</span>

        {{-- seta animada --}}
        <svg class="w-4 h-4 transition-transform duration-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd"
                d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06l-4.24 4.24a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08Z"
                clip-rule="evenodd" />
        </svg>
    </a>

    {{-- Submenu --}}
    <div data-dropdown-menu style="width: {{ $width }};"
        class="absolute -right-2 top-full z-50 mt-0 rounded-2xl border border-white/10 bg-sky-950 shadow-[0_18px_50px_rgba(0,0,0,.55)] overflow-hidden opacity-0 invisible pointer-events-none transition-all duration-150 nav-backdrop-24">

        <div class="absolute inset-x-0 h-3 -top-3"></div>

        {{-- filete dourado --}}
        <div class="h-[2px] w-full rounded-t-2xl nav-gold-gradient"></div>

        <div class="p-4">
            <div class="mb-0.5 text-xs font-extrabold tracking-widest text-right uppercase text-amber-200/80">
                {!! $description !!}
            </div>

            <div class="">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
