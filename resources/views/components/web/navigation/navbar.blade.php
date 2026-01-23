@php
    $logoSrc = request()->routeIs('web.ministry.kids-ee')
        ? asset('images/logo/kids-ee.webp')
        : asset('images/logo/ee-white.webp');
@endphp

<header id="main-header" class="fixed top-0 left-0 z-50 w-full text-white nav-textshadow">

    <div id="header-wrapper" class="transition-transform duration-300 ease-in-out translate-y-0 will-change-transform">

        {{-- =========================== FAIXA SUPERIOR =========================== --}}
        <div id="top-bar" class="hidden w-full transition-colors duration-300 shadow-none 2md:block"
            {{-- style="background-color: rgb(5, 47, 74)" --}}>
            <div class="flex items-center justify-end px-4 mx-auto max-w-8xl sm:px-6 lg:px-8">
                <div class="flex items-center gap-2">
                    @auth
                        <a href="{{ route('app.start') }}"
                            class="inline-flex items-center gap-2 py-2 text-sm font-semibold transition text-white/90 hover:text-amber-300">
                            {{ Auth::user()->name }}
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            class="inline-flex items-center gap-2 py-2 text-sm font-semibold transition text-white/90 hover:text-amber-300">
                            &#10023; {{ __('LOGIN') }} &#10023;
                        </a>
                    @endauth
                </div>
            </div>
        </div>

        {{-- =========================== BARRA PRINCIPAL =========================== --}}
        <div id="main-bar"
            class="border-t border-amber-500/80 transition-[background-color,box-shadow] duration-300 ease-in-out shadow-none">

            <div class="px-4 mx-auto max-w-8xl sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">

                    {{-- Brand --}}
                    <a href="{{ route('web.home') }}" class="flex items-center h-full gap-3">
                        <img src="{{ asset($logoSrc) }}" class="w-auto h-12 nav-iconshadow">
                        <div class="leading-tight">
                            <div class="relative text-white nav-cinzel">
                                EVANGELISMO EXPLOSIVO
                                <span
                                    class="{{ request()->routeIs('web.ministry.kids-ee') ? '' : 'hidden' }} absolute text-sm text-orange-400 rotate-45 -top-2 -right-4 font-averia-bold">Kids</span>
                            </div>
                            <div
                                class="hidden min-[345px]:flex min-[345px]:items-center min-[345px]:gap-1 text-xs font-bold bg-linear-to-r from-[#f5e6a8] via-[#d6b85f] to-[#b89b3c] bg-clip-text text-transparent tracking-wide">
                                NO BRASIL <span style="font-size: 9px;">&#10023;</span> Até Que
                                Todos Ouçam!
                            </div>
                        </div>
                    </a>

                    <x-web.navigation.menu-desktop />

                    {{-- ******* Botão Mobile ******* --}}
                    <button id="menu-btn"
                        class="p-2 rounded-md 2md:hidden hover:text-amber-300 focus:outline-none focus:ring focus:ring-amber-400/40"
                        aria-expanded="false" aria-label="Abrir menu">
                        <svg id="icon-hamburger" class="w-7 h-7" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-width="2" stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" />
                        </svg>
                        <svg id="icon-close" class="hidden w-7 h-7" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-width="2" stroke-linecap="round" d="M6 6l12 12M18 6L6 18" />
                        </svg>
                    </button>

                </div>
            </div>
        </div>


    </div>
    {{-- =========================== MENU MOBILE =========================== --}}
    <x-web.navigation.menu-mobile />
</header>
