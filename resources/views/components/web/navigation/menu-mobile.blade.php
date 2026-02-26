{{-- ******* Menu mobile ******* --}}
<div id="mobile-menu" class="fixed inset-0 hidden -z-[1] 2md:hidden bg-sky-950/90 backdrop-blur-lg">
    <div class="flex flex-col items-center w-full h-full">

        {{-- Links --}}
        <nav class="w-full px-6 py-24 space-y-5 overflow-auto text-lg font-extrabold">
            @auth
                <a href="{{ route('app.start') }}"
                    class="block pb-4 text-center js-close-menu text-amber-300 hover:text-amber-500">
                    <small class="mr-1">&#10023;</small> {{ Auth::user()->name }} <small class="mr-1">&#10023;</small>
                    <div class="text-xs font-light text-amber-100">{{ __('Plataforma Ministerial') }}</div>
                </a>
            @else
                <a href="{{ route('login') }}"
                    class="block pb-4 text-center js-close-menu text-amber-300 hover:text-amber-500">
                    <small class="px-1">&#10023;</small> {{ __('Login') }} <small class="px-1">&#10023;</small>
                    <div class="text-xs font-light text-amber-100">{{ __('Plataforma Ministerial') }}</div>
                </a>
            @endauth


            <a href="{{ route('web.home') }}" class="block js-close-menu hover:text-amber-300"><small
                    class="mr-1">&#10022; </small>
                Início</a>
            <a href="{{ route('web.home') }}#about" class="block js-close-menu hover:text-amber-300"><small
                    class="mr-1">&#10022;
                </small> O que é o
                EE?</a>
            <a href="{{ route('web.about.history') }}" class="block js-close-menu hover:text-amber-300"><small
                    class="mr-1">&#10022;
                </small>
                história</a>
            <a href="{{ route('web.about.faith') }}" class="block js-close-menu hover:text-amber-300"><small
                    class="mr-1">&#10022;
                </small>
                Declaração de
                Fé</a>
            <a href="{{ route('web.about.vision-mission') }}" class="block js-close-menu hover:text-amber-300"><small
                    class="mr-1">&#10022; </small>
                Visão,
                Missão e Princípios</a>
            <a href="{{ route('web.event.index') }}" class="block js-close-menu hover:text-amber-300"><small
                    class="mr-1">&#10022;
                </small>
                Eventos</a>
            <a href="{{ route('web.donate') }}" class="block js-close-menu hover:text-amber-300"><small
                    class="mr-1">&#10022;
                </small> Ofertas</a>
            <a href="https://evangelismexplosion.org" class="block js-close-menu hover:text-amber-300"
                target="_blank"><small class="mr-1">&#10022; </small> EE Internacional
            </a>

            <div class="flex flex-wrap gap-2 pt-10">
                <a href="{{ route('web.ministry.kids-ee') }}"
                    class="flex-auto px-8 py-2 text-center transition border rounded-lg shine js-close-menu border-white/20 text-white/90 hover:border-amber-400/60 hover:text-amber-300">
                    EE-Kids
                </a>

                <a href="{{ route('web.ministry.everyday-evangelism') }}"
                    class="flex-auto px-8 py-2 text-center transition border rounded-lg shine js-close-menu border-white/20 text-white/90 hover:border-amber-400/60 hover:text-amber-300">
                    Evangelismo Eficaz
                </a>

                <a href="{{ route('web.event.clinic-base') }}"
                    class="js-close-menu flex-auto shine px-8 py-2 font-semibold text-center rounded-lg text-[#1b1709] bg-linear-to-br from-[#f1d57a] via-[#c7a840] to-[#8a7424] border border-white/20 shadow-md shadow-black/40 transition hover:brightness-110 hover:shadow-black/60 nav-link-textshadow">
                    Como receber um evento de líderes
                </a>
            </div>
        </nav>

        <div class="fixed inset-x-0 bottom-0 py-3 text-sm text-center text-white/60 bg-sky-950/90 backdrop-blur-lg">
            © {{ date('Y') }} Evangelismo Explosivo no Brasil
        </div>
    </div>
</div>
