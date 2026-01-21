{{-- ******* Menu desktop ******* --}}
<nav class="items-center hidden h-16 gap-3.5 2md:gap-4 lg:gap-5 xl:gap-8 font-semibold 2md:flex">
    <a href="{{ route('web.home') }}"
        class="flex items-center h-full transition {{ request()->routeIs('web.home') ? 'text-amber-300' : 'text-white/90' }} hover:text-amber-300">
        Início
    </a>

    <x-src.dropdown label="EE" description="Detalhes do EE" width="fit-content" :isroute="request()->routeIs('web.about.*')">
        <x-src.dropdown-item label="&#10022; O que é o EE?" :route="route('web.home') . '#about'" />
        <x-src.dropdown-item :isroute="request()->routeIs('web.about.history')" label="&#10022; História" :route="route('web.about.history')" />
        <x-src.dropdown-item :isroute="request()->routeIs('web.about.faith')" label="&#10022; Declaração de Fé" :route="route('web.about.faith')" />
        <x-src.dropdown-item :isroute="request()->routeIs('web.about.vision-mission')" label="&#10022; Visão, Missão e Princípios" :route="route('web.about.vision-mission')" />
    </x-src.dropdown>

    <x-src.dropdown label="Ministérios" description="TODOS OS MINISTÉRIOS" :isroute="request()->routeIs('web.ministry.*')">
        <a href="{{ route('web.ministry.kids-ee') }}"
            class="flex items-start gap-3 p-3 transition rounded-xl {{ request()->routeIs('web.ministry.kids-ee') ? 'bg-white/5' : 'hover:bg-white/10' }} shine">
            <div>
                <div class="font-extrabold text-white border-b-2 border-orange-600/50 pb-0.5">
                    &#10022; EE-Kids <span class="pl-1 text-sm font-light opacity-80">«
                        Esperança Para
                        Crianças »</span>
                </div>
                <div class="text-xs leading-snug text-light text-white/60 pt-0.5 px-0.5">
                    Ministério de Evangelismo e Discipulado para
                    <strong>Crianças</strong>.
                </div>
            </div>
        </a>

        <a href="{{ route('web.ministry.everyday-evangelism') }}"
            class="flex items-start gap-3 p-3 transition rounded-xl {{ request()->routeIs('web.ministry.everyday-evangelism') ? 'bg-white/5' : 'hover:bg-white/10' }} shine">
            <div>
                <div class="font-extrabold text-white border-b-2 border-sky-600/50 pb-0.5">
                    &#10022; Evangelismo Eficaz <span class="pl-1 text-sm font-light opacity-80">« com 5
                        Partes »</span>
                </div>
                <div class="text-xs leading-snug text-light text-white/60 pt-0.5 px-0.5">
                    Ministério de Evangelismo e Discipulado para <strong>Jovens e
                        Adultos</strong>.
                </div>
            </div>
        </a>
    </x-src.dropdown>


    <a href="{{ route('web.event.index') }}"
        class="flex items-center h-full transition {{ request()->routeIs('web.event.*') ? 'text-amber-300' : 'text-white/90' }} hover:text-amber-300">
        Eventos
    </a>
    <a href="https://www.evangelismexplosion.org/" target="_blank"
        class="flex items-center h-full transition text-white/90 hover:text-amber-300">
        EE Internacional
    </a>
    <a href="{{ route('web.donate') }}" title="Oferta Missionária"
        class="h-9 flex items-center px-6 py-0.5 font-semibold text-center rounded text-sm text-[#1b1709]
                                   shine bg-linear-to-br from-[#f1d57a] via-[#c7a840] to-[#8a7424]
                                   border border-white/20 transition hover:brightness-110 nav-link-textshadow">
        <span class="text-2xl">&#10087;</span> Ofertas
    </a>
</nav>
