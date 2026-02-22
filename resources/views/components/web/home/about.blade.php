<section class="relative">
    <div aria-hidden="true"
        class="absolute w-1/3 rounded-full pointer-events-none -top-24 -right-24 h-4/5 blur-3xl opacity-50 z-0"
        style="background-image: radial-gradient(circle at 30% 30%,rgb(241, 213, 122),transparent 90%);">
    </div>

    <div aria-hidden="true"
        class="absolute w-1/3 rounded-full pointer-events-none -bottom-24 -left-24 h-4/5 blur-3xl opacity-50"
        style="background-image: radial-gradient(circle at 30% 30%,rgb(241, 213, 122),transparent 90%);">
    </div>
    <x-web.container id="about" class="relative">

        <div class="flex flex-col items-center gap-10 py-10 lg:flex-row group">
            <div class="w-full lg:w-1/2">
                <h2 class="relative text-2xl leading-snug" style="font-family: 'Cinzel', serif; font-weight: 400;">
                    O que é o <span class="text-[#8a7424] text-nowrap" style="font-weight: 500;">Evangelismo
                        Explosivo?</span>
                    <span
                        class="absolute left-0 -bottom-2 h-[2px] w-[min(28rem,100%)]
                       bg-linear-to-r from-[#b79d46] via-[#c7a84099] to-[#8a742455] opacity-90">
                    </span>
                </h2>

                <p class="mt-4 leading-relaxed text-slate-600">
                    O <strong>Evangelismo Explosivo (EE)</strong> é um ministério <em>internacional</em> e
                    <em>interdenominacional</em> centrado na igreja local que equipa pastores e leigos para viverem o
                    evangelismo como estilo de vida.
                    Fundamentado nos princípios de <em>II Timóteo 2:2</em>, busca alcançar pessoas para Cristo,
                    formar discípulos e promover uma multiplicação espiritual que fortalece a igreja
                    de maneira contínua e saudável.
                </p>

                <div
                    class="pt-4 mx-auto text-sm font-bold tracking-wider uppercase border-b 2md:mx-0 border-amber-950/30 w-fit text-amber-950/80">
                    As 4 fases do EE:
                </div>

                <ul class="mt-3 space-y-1 text-slate-600">
                    <li class="py-2 2md:py-0"><em>
                            <span class="block font-semibold text-amber-950 2md:inline"
                                style="font-family: 'Cinzel', serif;">» Amizade:</span>
                            <span class="block pl-3 2md:pl-0 2md:inline">relacionamentos genuínos como ponte para o
                                Evangelho.</span>
                        </em></li>
                    <li class="py-2 2md:py-0"><em>
                            <span class="block font-semibold text-amber-950 2md:inline"
                                style="font-family: 'Cinzel', serif;">» Evangelismo:</span>
                            <span class="block pl-3 2md:pl-0 2md:inline">apresentação clara e fiel das
                                Boas-Novas.</span>
                        </em></li>
                    <li class="py-2 2md:py-0"><em>
                            <span class="block font-semibold text-amber-950 2md:inline"
                                style="font-family: 'Cinzel', serif;">» Discipulado:</span>
                            <span class="block pl-3 2md:pl-0 2md:inline">acompanhamento intencional até a maturidade
                                cristã.</span>
                        </em></li>
                    <li class="py-2 2md:py-0"><em>
                            <span class="block font-semibold text-amber-950 2md:inline"
                                style="font-family: 'Cinzel', serif;">» Crescimento
                                saudável:</span>
                            <span class="block pl-3 2md:pl-0 2md:inline">igrejas fortalecidas pela multiplicação
                                espiritual.</span>
                        </em>
                    </li>
                </ul>

                <div class="flex flex-col gap-2 mt-6">
                    <a href="{{ route('web.about.history') }}"
                        class="inline-flex items-center gap-2 font-semibold w-fit
                      text-[#8a7424] hover:text-[#c7a840] transition
                      focus:outline-none focus:ring-2 focus:ring-amber-400/40 rounded-md">
                        Conheça nossa história

                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="w-4 h-4 opacity-0 -translate-x-1 transition-all duration-300 ease-out
                           group-hover:opacity-100 group-hover:translate-x-0
                           group-hover:animate-[arrow-pulse_1s_ease-in-out_infinite]"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>

                    <a href="{{ route('web.about.faith') }}"
                        class="inline-flex items-center gap-2 font-semibold w-fit
                      text-[#8a7424] hover:text-[#c7a840] transition
                      focus:outline-none focus:ring-2 focus:ring-amber-400/40 rounded-md">
                        Conheça nossa Declaração de Fé

                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="w-4 h-4 opacity-0 -translate-x-1 transition-all duration-300 ease-out
                           group-hover:opacity-100 group-hover:translate-x-0
                           group-hover:animate-[arrow-pulse_1s_ease-in-out_infinite]"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>

                    <a href="{{ route('web.about.vision-mission') }}"
                        class="inline-flex items-center gap-2 font-semibold w-fit
                      text-[#8a7424] hover:text-[#c7a840] transition
                      focus:outline-none focus:ring-2 focus:ring-amber-400/40 rounded-md">
                        Conheça nossa Visão, Missão & Princípios

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
            </div>

            <div class="w-full lg:w-1/2">
                <figure
                    class="w-full relative overflow-hidden rounded-lg shadow-md ring-1 ring-slate-900/10 border-t-4 border-r-4 border-white"
                    style="box-shadow: 3px -3px 0 #c7a840">
                    <img src="{{ asset('images/3rd_nations_congress_2016.webp') }}"
                        alt="Foto do Congresso das Nações realizado em 2016" class="w-full h-auto object-cover"
                        loading="lazy" decoding="async" />

                    <!-- overlay para contraste -->
                    <div class="absolute inset-0 bg-black/25"></div>

                    <!-- botão play (cobre a área toda para facilitar clique) -->
                    <button type="button" class="absolute inset-0 flex items-center justify-center group js-video-btn"
                        aria-label="Assistir vídeo sobre o Evangelismo Explosivo" data-video-id="tfgtlOQ4rGI">
                        <span
                            class="flex items-center justify-center w-16 h-16 rounded-full bg-white/90 text-slate-900
                       shadow-lg ring-1 ring-black/10 transition group-hover:scale-105 group-hover:bg-white">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor"
                                aria-hidden="true">
                                <path d="M8 5v14l11-7z"></path>
                            </svg>
                        </span>
                    </button>
                </figure>

                <figcaption class="mt-2 text-sm text-amber-800">
                    Foto do 3º Congresso das Nações, realizado em 2016.
                </figcaption>
            </div>

        </div>

        <x-web.modal-video />
        </x-webweb.container>
</section>
