@php
    $title = __('Oferta Missionária & Doações');
    $description =
        'Apoie o ministério Evangelismo Explosivo no Brasil — sua oferta nos ajuda a treinar igrejas, discipular pessoas e expandir o alcance do Evangelho.';
    $keywords = 'doações, contribuições, apoiar, evangelismo, EE Brasil';
    $ogImage = asset('images/og/donate.webp');
@endphp

<x-layouts.guest :title="$title" :description="$description" :keywords="$keywords" :ogImage="$ogImage">
    <x-web.header :title="$title" subtitle='Saiba como pode contribuir com nosso ministério' :cover="asset('images/clinic-ee.webp')" />

    @push('css')
        <style>
            @keyframes donate-fade-up {
                from {
                    opacity: 0;
                    transform: translateY(16px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .donate-fade-up {
                animation: donate-fade-up 900ms ease-out both;
            }

            .donate-delay-1 {
                animation-delay: 120ms;
            }

            .donate-delay-2 {
                animation-delay: 240ms;
            }

            .donate-delay-3 {
                animation-delay: 360ms;
            }
        </style>
    @endpush

    <section class="relative overflow-hidden -mt-10"
        style="background:
        radial-gradient(900px 520px at 15% 12%, rgba(199,168,64,.22), transparent 58%),
        radial-gradient(900px 520px at 85% 8%, rgba(241,213,122,.18), transparent 60%),
        linear-gradient(180deg, #052f4a 0%, #042033 45%, #031826 100%);">
        <div class="p-4 max-w-8xl mx-auto sm:px-6 lg:px-8 md:py-12">
            <div
                class="relative overflow-hidden border shadow-lg rounded-3xl bg-white/95 border-amber-500/30 shadow-black/20">
                <div class="grid gap-10 p-6 sm:p-10 lg:grid-cols-2">
                    <div class="donate-fade-up donate-delay-1 pt-4">
                        <h2 class="mt-4 text-3xl font-extrabold text-slate-900 sm:text-4xl">
                            Sua generosidade transforma igrejas, forma discípulos e alcança novas regiões do Brasil.
                        </h2>
                        <p class="mt-4 text-base leading-relaxed text-slate-700 font-averia-regular">
                            Cada contribuição sustenta treinamentos, produção de materiais e acompanhamento de
                            líderes locais. Juntos, fortalecemos a missão de levar o Evangelho com clareza e amor.
                        </p>

                        <div class="flex flex-wrap gap-3 mt-8">
                            <x-src.btn-gold label="Quero ofertar agora" route="#formas" />
                            <x-src.btn-silver label="Quero falar com a equipe" data-open-wa />
                        </div>

                        <div class="grid gap-4 mt-8 sm:grid-cols-3">
                            <div class="rounded-2xl border border-amber-500/20 bg-slate-50 px-4 py-3">
                                <p class="text-sm text-slate-500">Treinamentos apoiados</p>
                                <p class="mt-1 text-xl font-bold text-slate-900">+120</p>
                            </div>
                            <div class="rounded-2xl border border-amber-500/20 bg-slate-50 px-4 py-3">
                                <p class="text-sm text-slate-500">Líderes capacitados</p>
                                <p class="mt-1 text-xl font-bold text-slate-900">+3.500</p>
                            </div>
                            <div class="rounded-2xl border border-amber-500/20 bg-slate-50 px-4 py-3">
                                <p class="text-sm text-slate-500">Estados alcançados</p>
                                <p class="mt-1 text-xl font-bold text-slate-900">26</p>
                            </div>
                        </div>
                    </div>

                    <figure class="donate-fade-up donate-delay-2">
                        <img src="https://placehold.co/600x400"
                            alt="Equipe de treinamento reunida em oração e preparação"
                            class="object-cover w-full h-full shadow-md rounded-2xl ring-1 ring-slate-900/10"
                            loading="lazy" decoding="async" />
                    </figure>
                </div>
            </div>
        </div>
    </section>

    <section class="relative">
        <div class="p-4 max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="donate-fade-up donate-delay-1">
                    <h3 class="text-2xl text-slate-900 sm:text-3xl" style="font-family: 'Cinzel', serif;">
                        Onde sua oferta alcança
                    </h3>
                    <p class="mt-3 text-base leading-relaxed text-slate-700 font-averia-regular">
                        A oferta sustenta um movimento evangelistico em todo o Brasil: formamos multiplicadores,
                        fortalecemos igrejas e investimos em produção de novos materiais que facilitam o discipulado.
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-amber-500/25 bg-white/95 p-6 shadow-md shadow-black/10 donate-fade-up donate-delay-2">
                    <p class="text-sm font-semibold text-amber-700">Capacitação</p>
                    <h4 class="mt-2 text-xl font-bold text-slate-900">Treinamento de líderes locais</h4>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600 font-averia-regular">
                        Cursos presenciais e online para equipar pastores, líderes e equipes de evangelismo.
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-amber-500/25 bg-white/95 p-6 shadow-md shadow-black/10 donate-fade-up donate-delay-3">
                    <p class="text-sm font-semibold text-amber-700">Materiais</p>
                    <h4 class="mt-2 text-xl font-bold text-slate-900">Produção e distribuição</h4>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600 font-averia-regular">
                        Manuais, recursos digitais e kits de treinamento para fortalecer equipes em todo o Brasil.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section id="formas" class="relative">
        <div class="p-4 max-w-8xl mx-auto sm:px-6 lg:px-8">
            <x-web.container>
                <div class="grid gap-10 lg:grid-cols-2 lg:items-center">
                    <div class="donate-fade-up donate-delay-1">
                        <h3 class="text-2xl text-[#052f4a] sm:text-3xl" style="font-family: 'Cinzel', serif;">
                            Formas de contribuir
                        </h3>
                        <p class="mt-3 text-base leading-relaxed text-slate-700 font-averia-regular">
                            Escolha o formato que melhor se encaixa com sua realidade. Toda contribuição é
                            registrada e direcionada aos projetos missionários.
                        </p>

                        <div class="grid gap-4 mt-6">
                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-4">
                                <p class="text-sm font-semibold text-slate-900">Oferta única</p>
                                <p class="mt-2 text-sm leading-relaxed text-slate-600 font-averia-regular">
                                    Contribua com um valor pontual para apoiar um treinamento, evento ou projeto
                                    específico.
                                </p>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-4">
                                <p class="text-sm font-semibold text-slate-900">Parceiro mensal</p>
                                <p class="mt-2 text-sm leading-relaxed text-slate-600 font-averia-regular">
                                    Sustente o ministério continuamente e ajude a manter equipes, viagens e produção
                                    de materiais.
                                </p>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-4">
                                <p class="text-sm font-semibold text-slate-900">Igrejas e empresas</p>
                                <p class="mt-2 text-sm leading-relaxed text-slate-600 font-averia-regular">
                                    Parcerias estratégicas para projetos regionais, eventos e treinamentos completos.
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-3 mt-8">
                            <x-src.btn-gold label="Solicitar dados para oferta" route="#contato" />
                            <x-src.btn-silver label="Entender projetos apoiados" route="#projetos" />
                        </div>
                    </div>

                    <div class="donate-fade-up donate-delay-2">
                        <div
                            class="relative overflow-hidden border rounded-2xl bg-slate-50 shadow-md border-amber-500/20">
                            <div
                                class="absolute inset-x-0 top-0 h-1 bg-linear-to-r from-[#8a7424] via-[#c7a840] to-[#f1d57a]">
                            </div>
                            <div class="p-6">
                                <h4 class="text-lg font-semibold text-slate-900">oferta rápida (Pix / QR Code)</h4>
                                <p class="mt-2 text-sm leading-relaxed text-slate-600 font-averia-regular">
                                    Inclua aqui a chave Pix, QR code ou instruções para pagamento. Se precisar de
                                    apoio, nossa equipe responde rápido.
                                </p>
                                <img src="https://placehold.co/600x400" alt="Espaço para QR Code de oferta"
                                    class="object-cover w-full h-56 mt-6 rounded-xl ring-1 ring-slate-900/10"
                                    loading="lazy" decoding="async" />
                                <div class="mt-5 flex flex-wrap gap-3">
                                    <x-src.btn-gold label="Copiar chave Pix" route="#contato" />
                                    <x-src.btn-silver label="Preciso de ajuda" route="#contato" data-open-wa />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </x-web.container>
        </div>
    </section>

    <section id="projetos" class="relative">
        <div class="p-4 max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-col gap-6">
                <div class="donate-fade-up donate-delay-1">
                    <h3 class="text-2xl text-slate-900 sm:text-3xl" style="font-family: 'Cinzel', serif;">
                        Projetos impulsionados pela sua oferta
                    </h3>
                    <p class="mt-3 text-base leading-relaxed text-slate-700 font-averia-regular">
                        Sua generosidade fortalece iniciativas locais que multiplicam discípulos e promovem a unidade
                        entre igrejas.
                    </p>
                </div>

                <div class="grid gap-6 lg:grid-cols-3">
                    <article class="overflow-hidden border shadow-md rounded-2xl bg-white/95 border-amber-500/20">
                        <img src="https://placehold.co/600x400" alt="Equipe em sala de treinamento missionário"
                            class="object-cover w-full h-48" loading="lazy" decoding="async" />
                        <div class="flex flex-col gap-3 p-5">
                            <h4 class="text-lg font-semibold text-slate-900">Treinamentos regionais</h4>
                            <p class="text-sm leading-relaxed text-slate-600 font-averia-regular">
                                Encontros presenciais para capacitar líderes e formar novas equipes de evangelismo.
                            </p>
                        </div>
                    </article>

                    <article class="overflow-hidden border shadow-md rounded-2xl bg-white/95 border-amber-500/20">
                        <img src="https://placehold.co/600x400" alt="Distribuição de materiais de discipulado"
                            class="object-cover w-full h-48" loading="lazy" decoding="async" />
                        <div class="flex flex-col gap-3 p-5">
                            <h4 class="text-lg font-semibold text-slate-900">Materiais e recursos</h4>
                            <p class="text-sm leading-relaxed text-slate-600 font-averia-regular">
                                Impressão, traduções e recursos digitais que acompanham cada etapa do treinamento.
                            </p>
                        </div>
                    </article>

                    <article class="overflow-hidden border shadow-md rounded-2xl bg-white/95 border-amber-500/20">
                        <img src="https://placehold.co/600x400" alt="Equipe de apoio visitando igrejas"
                            class="object-cover w-full h-48" loading="lazy" decoding="async" />
                        <div class="flex flex-col gap-3 p-5">
                            <h4 class="text-lg font-semibold text-slate-900">Acompanhamento de igrejas</h4>
                            <p class="text-sm leading-relaxed text-slate-600 font-averia-regular">
                                Mentoria e suporte contínuo para que cada igreja implemente o evangelismo como estilo
                                de vida.
                            </p>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </section>

    <section class="relative">
        <div class="p-4 max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-2 lg:items-center">
                <figure class="donate-fade-up donate-delay-1">
                    <img src="https://placehold.co/600x400" alt="Momento de oração com equipe ministerial"
                        class="object-cover w-full h-full shadow-md rounded-2xl ring-1 ring-slate-900/10"
                        loading="lazy" decoding="async" />
                </figure>
                <div class="donate-fade-up donate-delay-2">
                    <h3 class="text-2xl text-slate-900 sm:text-3xl" style="font-family: 'Cinzel', serif;">
                        Testemunho pessoal
                    </h3>
                    <p class="mt-3 text-base leading-relaxed text-slate-700 font-averia-regular">
                        "Nossa igreja encontrou um novo fôlego para evangelizar. O treinamento mudou nossa cultura e
                        hoje discipulamos novos líderes com consistência."
                    </p>
                    <p class="mt-4 text-sm font-semibold text-amber-700">Pastor e líder local</p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <x-src.btn-gold label="Quero gerar esse impacto" route="#formas" />
                        <x-src.btn-silver label="Conhecer o ministério" route="#contato" data-open-wa />
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="contato" class="relative">
        <div class="p-4 max-w-8xl mx-auto sm:px-6 lg:px-8">
            <x-web.container>
                <div class="grid gap-8 lg:grid-cols-2 lg:items-center">
                    <div class="donate-fade-up donate-delay-1">
                        <h3 class="text-2xl text-[#052f4a] sm:text-3xl" style="font-family: 'Cinzel', serif;">
                            Transparência e cuidado com sua oferta
                        </h3>
                        <p class="mt-3 text-base leading-relaxed text-slate-700 font-averia-regular">
                            Compartilhamos relatórios com os adotandes, organizamos projetos com clareza e acompanhamos
                            cada etapa para que sua oferta gere frutos duradouros.
                        </p>
                        <div class="grid gap-3 mt-6">
                            <div class="flex items-start gap-3">
                                <span class="mt-2 h-2 w-2 rounded-full bg-amber-500"></span>
                                <p class="text-sm text-slate-700 font-averia-regular">
                                    Prestação de contas regular para parceiros e igrejas.
                                </p>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="mt-2 h-2 w-2 rounded-full bg-amber-500"></span>
                                <p class="text-sm text-slate-700 font-averia-regular">
                                    Alocação de recursos de acordo com prioridades missionárias.
                                </p>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="mt-2 h-2 w-2 rounded-full bg-amber-500"></span>
                                <p class="text-sm text-slate-700 font-averia-regular">
                                    Acompanhamento de projetos e histórias de impacto.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="donate-fade-up donate-delay-2">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6 shadow-sm">
                            <h4 class="text-lg font-semibold text-slate-900">Fale com nossa equipe</h4>
                            <p class="mt-2 text-sm leading-relaxed text-slate-600 font-averia-regular">
                                Informe a melhor forma de contato para receber orientações sobre doações,
                                parcerias e apoio a projetos locais.
                            </p>
                            <div class="grid gap-3 mt-6">
                                <div class="rounded-xl border border-amber-500/20 bg-white px-4 py-3">
                                    <p class="text-xs font-semibold text-amber-700">E-mail</p>
                                    <p class="mt-1 text-sm text-slate-700">eebrasil@eebrasil.org.br</p>
                                </div>
                                <div class="rounded-xl border border-amber-500/20 bg-white px-4 py-3">
                                    <p class="text-xs font-semibold text-amber-700">WhatsApp</p>
                                    <p class="mt-1 text-sm text-slate-700">Atendimento rápido e personalizado</p>
                                </div>
                            </div>
                            <div class="mt-6 flex flex-wrap gap-3">
                                <x-src.btn-gold label="Quero receber orientações" route="#contato" data-open-wa />
                                <x-src.btn-silver label="Baixar relatório" route="#contato" />
                            </div>
                        </div>
                    </div>
                </div>
            </x-web.container>
        </div>
    </section>
</x-layouts.guest>
