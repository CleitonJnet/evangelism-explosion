    {{-- ========================= SECTION 1 — O que é EE-Kids + EPC ========================= --}}
    <section id="o-que-e" class="bg-fixed bg-center bg-no-repeat bg-cover"
        style="background-image: url({{ asset('images/ee-kids/Graphic-yellow-dove.png') }});">
        <div class="px-4 mx-auto max-w-8xl mx-auto sm:px-6 lg:px-8 py-14 lg:py-20">
            <div class="flex flex-col items-center mb-10">
                <img src="{{ asset('images/logo/hope-for-kids.webp') }}" alt="EPC"
                    class="object-contain w-full px-2 xs:px-4 sm:w-3/5 sm:px-0"
                    style="filter: drop-shadow(0 0 10px #fff)">

                <p class="max-w-2xl text-xl font-extrabold text-center text-white/85 text-shadow-dark">
                    Este ministério de capacitação ensina as crianças a amar Jesus e a compartilhar Seu amor com
                    outras pessoas.
                </p>
            </div>

            <div class="grid items-center gap-10 md:grid-cols-12">
                {{-- texto --}}
                <div class="md:col-span-7">

                    <h1
                        class="mt-5 text-3xl text-white reveal sm:text-4xl lg:text-5xl drop-shadow text-shadow-dark font-averia-bold">
                        Crianças podem viver o Evangelho,
                        <span class="text-amber-300">e compartilhar a Boa Nova de Salvação.</span>
                    </h1>

                    <p class="mt-5 text-lg font-bold leading-relaxed reveal text-white/85 text-shadow-dark">
                        O Esperança Para Crianças (EPC) é o programa do EE-Kids que a igreja implementa localmente após
                        capacitar seus líderes. Com materiais prontos e um plano claro, o EPC orienta 12 encontros com
                        as crianças, combinando ensino bíblico, atividades lúdicas e prática supervisionada, para que
                        elas compreendam o Evangelho, firmem a fé e aprendam a compartilhar as Boas Novas com confiança.
                    </p>

                    <div class="flex flex-col gap-3 mt-8 reveal sm:flex-row">
                        {{-- <a href="#o-que-e"
                            class="inline-flex items-center justify-center px-5 py-3 font-bold text-white transition shadow shine rounded-xl bg-amber-400 hover:shadow-lg">
                            Começar a apresentação
                        </a> --}}
                        <button type="button" data-open-wa
                            class="inline-flex items-center justify-center px-5 py-3 font-semibold transition border text-amber-950 rounded-xl shine bg-white/25 border-white/50 hover:bg-white/40">
                            Solicitar Workshop de Liderança
                        </button>
                    </div>

                    {{-- mini métricas (conteúdo “de manual”: foco em líderes, professores, crianças) --}}
                    <div class="grid gap-4 mt-10 reveal sm:grid-cols-3 text-shadow-dark">
                        <div class="p-4 border rounded-2xl bg-white/8 border-white/15">
                            <p class="text-xs text-white/70">Treinamento base</p>
                            <p class="mt-1 text-2xl font-extrabold text-white">20h</p>
                            <p class="text-xs text-white/70">em 2 dias</p>
                        </div>
                        <div class="p-4 border rounded-2xl bg-white/8 border-white/15">
                            <p class="text-xs text-white/70">Comece com</p>
                            <p class="mt-1 text-2xl font-extrabold text-white">1 a 3</p>
                            <p class="text-xs text-white/70">líderes-chave</p>
                        </div>
                        <div class="p-4 border rounded-2xl bg-white/8 border-white/15">
                            <p class="text-xs text-white/70">Resultado</p>
                            <p class="mt-1 text-2xl font-extrabold text-white">Igreja</p>
                            <p class="text-xs text-white/70">pronta para implementar</p>
                        </div>
                    </div>
                </div>

                {{-- card brochure / materiais (estilo “download brochure”) --}}
                <div class="md:col-span-5">
                    <div class="relative overflow-hidden border shadow-2xl reveal rounded-3xl border-white/15">
                        <div class="absolute inset-0"
                            style="background-image:url('{{ asset('images/ee-kids/kids-bg-blue.png') }}'); background-size:cover; background-position:center;">
                        </div>

                        <div class="relative p-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-xl font-extrabold leading-tight text-white/90 text-shadow-dark">
                                        Workshop EPC
                                    </p>
                                    <p class="mt-1 text-sm text-white/85 text-shadow-dark">
                                        Com materiais e visão do ministério
                                    </p>
                                </div>
                                <div class="px-3 py-2 border bg-white/15 border-white/20 rounded-xl">
                                    <p class="text-xs font-black tracking-wide text-white">TREINAMENTO</p>
                                </div>
                            </div>

                            <div
                                class="gap-4 p-3 mt-5 border lg:flex lg:items-center rounded-2xl bg-white/10 border-white/20">
                                <img src="{{ asset('images/ee-kids/handbook-hope-for-kids-workshop.webp') }}"
                                    alt="Brochure / Handbook" class="block object-contain lg:w-1/2">
                                <div class="flex-1">
                                    <p class="text-lg font-extrabold leading-tight text-white text-shadow-dark">
                                        Conheça Workshop Esperança Para Crianças
                                    </p>
                                    <p class="mt-1 text-sm leading-relaxed text-white/85 text-shadow-dark">
                                        Uma visão clara do programa, do Workshop de Liderança e do que sua igreja
                                        precisa para começar.
                                    </p>
                                </div>
                            </div>
                            <a href="{{ route('web.event.clinic-base') }}"
                                class="inline-flex items-center justify-center w-full px-4 py-3 mt-3 font-bold text-white transition bg-red-600 shadow shine rounded-xl hover:bg-red-700">
                                Veja como levar o EPC para sua igreja
                            </a>

                        </div>
                    </div>

                </div>
            </div>

        </div>
    </section>
