@php
    $title = __('Oferta Missionária & Doações');
    $description =
        'Apoie o ministério Evangelismo Explosivo no Brasil: sua oferta nos ajuda a treinar igrejas, discipular pessoas e expandir o alcance do Evangelho.';
    $keywords = 'doações, contribuições, apoiar, evangelismo, EE Brasil';
    $ogImage = asset('images/og/donate.webp');
@endphp

<x-layouts.guest :title="$title" :description="$description" :keywords="$keywords" :ogImage="$ogImage" class="space-y-0!">
    <x-web.header :title="$title"
        subtitle='<span class="max-w-xl mx-auto block">Você pode ser um parceiro do ministério de <strong>Evangelismo Explosivo Internacional no Brasil</strong></span>'
        :cover="asset('images/clinic-ee.webp')" />

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
                        <p class="mt-4 text-base leading-relaxed text-slate-700">
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

                    {{-- VÍDEO 1 (thumbnail + play) --}}
                    <figure
                        class="donate-fade-up donate-delay-2 relative overflow-hidden rounded-2xl ring-1 ring-slate-900/10 border-y-[3px] border-r-[3px] border-white"
                        style="box-shadow: 3px 0 0 #c7a840">
                        <img src="https://placehold.co/600x400?text=Video institucional"
                            alt="Equipe de treinamento reunida em oração e preparação"
                            class="object-cover w-full h-full" loading="lazy" decoding="async" />

                        <div class="absolute inset-0 bg-black/25"></div>

                        <button type="button"
                            class="absolute inset-0 flex items-center justify-center group js-video-btn"
                            aria-label="Assistir vídeo institucional" data-video-id="tfgtlOQ4rGI">
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
                </div>
            </div>
        </div>
    </section>

    <section class="relative ee-metal-section py-12">
        <div class="p-4 max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="donate-fade-up donate-delay-1">
                    <h3 class="text-2xl text-slate-900 sm:text-3xl" style="font-family: 'Cinzel', serif;">
                        Onde sua oferta alcança
                    </h3>
                    <p class="mt-3 text-base leading-relaxed text-slate-700">
                        A oferta sustenta um movimento evangelístico em todo o Brasil: formamos multiplicadores,
                        fortalecemos igrejas e investimos em produção de novos materiais que facilitam o discipulado.
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-amber-500/25 bg-white/95 p-6 shadow-md shadow-black/10 donate-fade-up donate-delay-2">
                    <p class="text-sm font-semibold text-amber-700">Capacitação</p>
                    <h4 class="mt-2 text-xl font-bold text-slate-900">Treinamento de líderes locais</h4>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600">
                        Cursos presenciais e online para equipar pastores, líderes e equipes de evangelismo.
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-amber-500/25 bg-white/95 p-6 shadow-md shadow-black/10 donate-fade-up donate-delay-3">
                    <p class="text-sm font-semibold text-amber-700">Materiais</p>
                    <h4 class="mt-2 text-xl font-bold text-slate-900">Produção e distribuição</h4>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600">
                        Manuais, recursos digitais e kits de treinamento para fortalecer equipes em todo o Brasil.
                    </p>
                </div>
            </div>
        </div>
        <div aria-hidden="true"
            class="donate-fade-up donate-delay-1 absolute inset-x-0 bottom-0 h-1 pointer-events-none bg-linear-to-r from-transparent via-amber-500 to-transparent">
        </div>
    </section>

    <div id="divisor" class="h-10"></div>

    <section id="formas"
        class="max-w-8xl mx-auto sm:px-6 lg:px-8 space-y-8 donate-fade-up donate-delay-1 bg-white p-8 rounded-lg relative py-16">

        <div class="text-center max-w-3xl mx-auto">
            <h3 class="text-2xl text-[#052f4a] sm:text-3xl" style="font-family: 'Cinzel', serif;">
                <span style="color: #a58621">Formas de</span> contribuir
            </h3>

            <p class="text-base leading-relaxed text-slate-700">
                Escolha o formato que melhor se encaixa com sua realidade. Toda contribuição é
                registrada e direcionada aos projetos missionários.
            </p>
        </div>

        <div class="flex flex-wrap gap-4">
            <div class="rounded-xl border border-slate-200 ee-metal-section px-4 py-4 grow basis-56">
                <p class="text-sm text-amber-800 font-light" style="text-shadow: 1px 1px 1px #fff">
                    OFERTA <span class="font-bold">ÚNICA</span>
                </p>
                <p class="mt-2 text-sm leading-relaxed text-slate-800">
                    Contribua com um valor pontual para apoiar um treinamento, evento ou projeto
                    específico.
                </p>
            </div>
            <div class="rounded-xl border border-slate-200 ee-metal-section px-4 py-4 grow basis-56">
                <p class="text-sm text-amber-800 font-light" style="text-shadow: 1px 1px 1px #fff">
                    PARCEIRO <span class="font-bold">MENSAL</span>
                </p>
                <p class="mt-2 text-sm leading-relaxed text-slate-800">
                    Sustente o ministério continuamente e ajude a manter equipes, viagens e produção
                    de materiais.
                </p>
            </div>
            <div class="rounded-xl border border-slate-200 ee-metal-section px-4 py-4 grow basis-56">
                <p class="text-sm text-amber-800 font-light" style="text-shadow: 1px 1px 1px #fff">
                    PROJETOS <span class="font-bold">ESPECÍFICOS</span>
                </p>
                <p class="mt-2 text-sm leading-relaxed text-slate-800">
                    Parcerias estratégicas para projetos regionais, eventos e treinamentos completos.
                </p>
            </div>
        </div>

        <div class="flex flex-wrap justify-end gap-3">
            <x-src.btn-silver label="Entender projetos apoiados" route="#projetos" />
        </div>

        <div
            class="relative p-6 flex flex-col md:flex-row gap-6 overflow-hidden border rounded-2xl bg-sky-950 shadow-md border-amber-500/20">
            <div class="absolute inset-x-0 top-0 h-1 bg-linear-to-r from-[#8a7424] via-[#c7a840] to-[#f1d57a]"></div>

            <div class="mx-auto md:mx-0 flex justify-center items-center">
                <img src="{{ asset('images/qrcode-pix-ee.webp') }}" alt="QR Code PIX do EE-Brasil"
                    class="object-contain max-h-60 border-8 rounded-xl border-white" loading="lazy" decoding="async" />
            </div>

            <div>
                <p class="mt-2 font-bold leading-relaxed text-slate-100">
                    Escaneie o QR Code ou copie a chave Pix para realizar sua oferta. Se precisar de
                    apoio, nossa equipe responde rápido.
                </p>
                <div class="mt-5 rounded-xl border border-slate-200 bg-white p-4">
                    <div class="flex items-center gap-2">
                        <span
                            class="inline-flex items-center rounded-full bg-sky-950 px-2.5 py-1 text-xs font-semibold text-white">
                            Chave PIX
                        </span>
                        <span class="text-xs text-slate-500">Copie e cole</span>
                    </div>
                    <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-start relative">
                        <span data-pix-key
                            class="w-full rounded-xl bg-slate-50 px-3 py-2 text-sm text-slate-700 ring-1 ring-slate-300">
                            eebrasil@eebrasil.org.br
                        </span>
                        <span
                            class="hidden inset-0 text-sm font-bold text-white absolute bottom-0 bg-sky-950/50 justify-center items-center px-4 rounded-xl backdrop-blur-[1px]"
                            data-copy-feedback>
                            Chave PIX copiada
                        </span>
                    </div>
                    <div class="mt-5 flex flex-wrap gap-3">
                        <x-src.btn-gold label="Copiar chave Pix" type="button" data-copy-pix />
                        <x-src.btn-silver label="Preciso de ajuda" route="#contato" data-open-wa />
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="projetos" class="relative py-12">
        <div class="p-4 max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-col gap-6">
                <div class="donate-fade-up donate-delay-1">
                    <h3 class="text-2xl text-slate-900 sm:text-3xl" style="font-family: 'Cinzel', serif;">
                        Projetos <span style="color: #a58621">impulsionados</span> pela sua oferta
                    </h3>
                    <p class="mt-3 text-base leading-relaxed text-slate-700">
                        Sua generosidade fortalece iniciativas locais que multiplicam discípulos e promovem a unidade
                        entre igrejas.
                    </p>
                </div>

                <div class="grid gap-6 lg:grid-cols-3">
                    <article class="overflow-hidden border shadow-md rounded-2xl bg-white/95 border-amber-500/20">
                        <img src="https://placehold.co/600x400?text=Foto de Treinamento"
                            alt="Equipe em sala de treinamento missionário" class="object-cover w-full h-48"
                            loading="lazy" decoding="async" />
                        <div class="flex flex-col gap-3 p-5">
                            <h4 class="text-lg font-semibold text-slate-900">Treinamentos regionais</h4>
                            <p class="text-sm leading-relaxed text-slate-600">
                                Encontros presenciais para capacitar líderes e formar novas equipes de evangelismo.
                            </p>
                        </div>
                    </article>

                    <article class="overflow-hidden border shadow-md rounded-2xl bg-white/95 border-amber-500/20">
                        <img src="https://placehold.co/600x400?text=Foto de igreja recebendo os kits"
                            alt="Distribuição de materiais de discipulado" class="object-cover w-full h-48"
                            loading="lazy" decoding="async" />
                        <div class="flex flex-col gap-3 p-5">
                            <h4 class="text-lg font-semibold text-slate-900">Materiais e recursos</h4>
                            <p class="text-sm leading-relaxed text-slate-600">
                                Impressão, traduções e recursos digitais que acompanham cada etapa do treinamento.
                            </p>
                        </div>
                    </article>

                    <article class="overflow-hidden border shadow-md rounded-2xl bg-white/95 border-amber-500/20">
                        <img src="https://placehold.co/600x400?text=Foto de Mentoria pós treinamento"
                            alt="Equipe de apoio visitando igrejas" class="object-cover w-full h-48" loading="lazy"
                            decoding="async" />
                        <div class="flex flex-col gap-3 p-5">
                            <h4 class="text-lg font-semibold text-slate-900">Acompanhamento de igrejas</h4>
                            <p class="text-sm leading-relaxed text-slate-600">
                                Mentoria e suporte contínuo para que cada igreja implemente todas as ferramentas do
                                ministério do EE como estilo de vida.
                            </p>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </section>

    <x-src.line-theme class="px-6 mg:px-10 max-w-7xl" />

    <section class="relative py-12">
        <div class="p-4 max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-2 lg:items-center">

                {{-- VÍDEO 2 (thumbnail + play) --}}
                <figure
                    class="donate-fade-up donate-delay-1 relative overflow-hidden rounded-2xl ring-1 ring-slate-900/10 border-b-[3px] border-l-[3px] border-white"
                    style="box-shadow: -3px 3px 0 #c7a840">
                    <img src="https://placehold.co/600x400?text=Video+de+Igreja+que+foi+Equipada"
                        alt="Momento de oração com equipe ministerial" class="object-cover w-full h-full shadow-md"
                        loading="lazy" decoding="async" />

                    <div class="absolute inset-0 bg-black/25"></div>

                    <button type="button"
                        class="absolute inset-0 flex items-center justify-center group js-video-btn"
                        aria-label="Assistir vídeo de testemunho" data-video-id="fbqGL_8AOpw">
                        {{-- https://youtu.be/fbqGL_8AOpw --}}
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

                <div class="donate-fade-up donate-delay-2">
                    <h3 class="text-2xl text-slate-900 sm:text-3xl" style="font-family: 'Cinzel', serif;">
                        Testemunho pessoal
                    </h3>
                    <p class="mt-3 text-base leading-relaxed text-slate-700">
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
                        <p class="mt-3 text-base leading-relaxed text-slate-700">
                            Compartilhamos relatórios com os adotandes, organizamos projetos com clareza e acompanhamos
                            cada etapa para que sua oferta gere frutos duradouros.
                        </p>
                        <div class="grid gap-3 mt-6">
                            <div class="flex items-start gap-3">
                                <span class="mt-2 h-2 w-2 rounded-full bg-amber-500"></span>
                                <p class="text-sm text-slate-700">
                                    Prestação de contas regular para parceiros e igrejas.
                                </p>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="mt-2 h-2 w-2 rounded-full bg-amber-500"></span>
                                <p class="text-sm text-slate-700">
                                    Alocação de recursos de acordo com prioridades missionárias.
                                </p>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="mt-2 h-2 w-2 rounded-full bg-amber-500"></span>
                                <p class="text-sm text-slate-700">
                                    Acompanhamento de projetos e histórias de impacto.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="donate-fade-up donate-delay-2">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6 shadow-sm">
                            <h4 class="text-lg font-semibold text-slate-900">Fale com nossa equipe</h4>
                            <p class="mt-2 text-sm leading-relaxed text-slate-600">
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
                                <x-src.btn-gold label="Quero receber orientações" data-open-wa />
                                <x-src.btn-silver label="Baixar relatório" route="#contato" />
                            </div>
                        </div>
                    </div>
                </div>
            </x-web.container>
        </div>
    </section>

    {{-- MODAL ÚNICO REUTILIZÁVEL (serve para todos os vídeos) --}}
    <div id="videoModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/70" data-close-modal></div>

        <div class="relative mx-auto flex min-h-screen max-w-4xl items-center px-4">
            <div class="relative w-full overflow-hidden rounded-2xl bg-black shadow-2xl">
                <button type="button"
                    class="absolute right-3 top-3 z-10 rounded-full bg-white/90 px-3 py-1 text-sm font-semibold text-slate-900 ring-1 ring-black/10 hover:bg-white"
                    data-close-modal aria-label="Fechar vídeo">
                    Fechar ✕
                </button>

                <div class="aspect-video w-full">
                    <iframe id="videoFrame" class="h-full w-full" src="" title="Vídeo do YouTube"
                        frameborder="0" allow="autoplay; encrypted-media; picture-in-picture"
                        allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>

    @push('js')
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                const modal = document.getElementById("videoModal");
                const frame = document.getElementById("videoFrame");

                if (!modal || !frame) return;

                function openVideoModal(videoId) {
                    // autoplay=1 inicia ao abrir; mute=1 aumenta compatibilidade de autoplay
                    frame.src = `https://www.youtube.com/embed/${videoId}?autoplay=1&mute=1&rel=0`;
                    modal.classList.remove("hidden");
                    document.body.style.overflow = "hidden";
                }

                function closeVideoModal() {
                    modal.classList.add("hidden");
                    frame.src = "";
                    document.body.style.overflow = "";
                }

                // Delegação de evento: vale para qualquer .js-video-btn em qualquer lugar da página
                document.addEventListener("click", (e) => {
                    const videoBtn = e.target.closest(".js-video-btn");
                    if (videoBtn) {
                        const id = videoBtn.dataset.videoId;
                        if (id) openVideoModal(id);
                        return;
                    }

                    if (e.target.closest("[data-close-modal]")) {
                        closeVideoModal();
                    }
                });

                document.addEventListener("keydown", (e) => {
                    if (e.key === "Escape") closeVideoModal();
                });
            });
        </script>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const copyButton = document.querySelector('[data-copy-pix]');
                const pixKey = document.querySelector('[data-pix-key]');
                const copyFeedback = document.querySelector('[data-copy-feedback]');

                if (!copyButton || !pixKey || !copyFeedback) {
                    return;
                }

                const showFeedback = () => {
                    copyFeedback.classList.remove('hidden');
                    copyFeedback.classList.add('inline-flex');
                    setTimeout(() => {
                        copyFeedback.classList.add('hidden');
                        copyFeedback.classList.remove('inline-flex');
                    }, 2000);
                };

                const fallbackCopy = (text) => {
                    const tempInput = document.createElement('textarea');
                    tempInput.value = text;
                    tempInput.setAttribute('readonly', '');
                    tempInput.style.position = 'absolute';
                    tempInput.style.left = '-9999px';
                    document.body.appendChild(tempInput);
                    tempInput.select();
                    tempInput.setSelectionRange(0, tempInput.value.length);
                    const success = document.execCommand('copy');
                    document.body.removeChild(tempInput);
                    return success;
                };

                copyButton.addEventListener('click', async () => {
                    const pixValue = pixKey.textContent?.trim() ?? '';
                    if (!pixValue) {
                        return;
                    }

                    try {
                        if (navigator.clipboard?.writeText) {
                            await navigator.clipboard.writeText(pixValue);
                            showFeedback();
                            return;
                        }

                        if (fallbackCopy(pixValue)) {
                            showFeedback();
                        }
                    } catch (error) {
                        console.warn('Nao foi possivel copiar a chave PIX.', error);
                    }
                });
            });
        </script>
    @endpush

</x-layouts.guest>
