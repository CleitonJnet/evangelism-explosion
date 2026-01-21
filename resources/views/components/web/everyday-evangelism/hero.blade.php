@push('css')
    <style>
        /* =========================== HERO — Evangelismo Eficaz =========================== */

        /* Fundo prata “escovado” com brilhos dourados discretos */
        .ee-hero-bg {
            background:
                radial-gradient(900px 520px at 18% 22%, rgba(241, 213, 122, .32), transparent 58%),
                radial-gradient(800px 520px at 82% 18%, rgba(199, 168, 64, .18), transparent 60%),
                radial-gradient(1000px 700px at 60% 85%, rgba(15, 23, 42, .10), transparent 60%),
                linear-gradient(180deg, #F8FAFC 0%, #EEF2F7 45%, #E6EBF2 100%);
            color: #0f172a;
        }

        /* Card “metal” com relevo leve */
        .ee-metal-card {
            background:
                linear-gradient(135deg,
                    rgba(255, 255, 255, .92) 0%,
                    rgba(248, 250, 252, .70) 40%,
                    rgba(226, 232, 240, .62) 100%);
            border: 1px solid rgba(15, 23, 42, .10);
            box-shadow:
                0 26px 60px rgba(2, 6, 23, .10),
                inset 0 1px 0 rgba(255, 255, 255, .75);
        }

        /* Filete dourado como assinatura visual */
        .ee-gold-edge {
            background: linear-gradient(180deg,
                    rgba(241, 213, 122, .55),
                    rgba(199, 168, 64, .75),
                    rgba(138, 116, 36, .55));
        }

        /* Chips / Tags */
        .ee-chip {
            background: linear-gradient(135deg,
                    rgba(255, 255, 255, .70),
                    rgba(226, 232, 240, .55));
            border: 1px solid rgba(199, 168, 64, .45);
            color: #7c2d12;
        }

        /* =========================================================
                                                                                                                                                                                                                                                                           BOTÕES — padrão do menu (dourado forte)
                                                                                                                                                                                                                                                                           ========================================================= */
        .ee-btn-gold {
            position: relative;
            overflow: hidden;

            /* MESMO gradiente do botão do menu */
            background: linear-gradient(135deg, #f1d57a, #c7a840, #8a7424);
            border: 1px solid rgba(255, 255, 255, .20);
            color: #1b1709;

            box-shadow:
                0 12px 28px rgba(0, 0, 0, .22),
                inset 0 1px 0 rgba(255, 255, 255, .45);

            transition: filter .18s ease, transform .18s ease, box-shadow .18s ease;
            text-shadow: 1px 1px 2px rgba(255, 255, 255, .9);
        }

        .ee-btn-gold:hover {
            filter: brightness(1.10);
            transform: translateY(-1px);
            box-shadow:
                0 14px 34px rgba(0, 0, 0, .26),
                inset 0 1px 0 rgba(255, 255, 255, .55);
        }

        /* Botão secundário: prata nobre */
        .ee-btn-silver {
            background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 45%, #e2e8f0 100%);
            color: #0f172a;
            border: 1px solid rgba(15, 23, 42, .22);
            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, .75),
                0 8px 18px rgba(0, 0, 0, .12);
            transition: background .18s ease, transform .18s ease, box-shadow .18s ease;
        }

        .ee-btn-silver:hover {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            transform: translateY(-1px);
            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, .80),
                0 10px 22px rgba(0, 0, 0, .14);
        }
    </style>
@endpush

{{-- HERO --}}
<section
    style="background:
        radial-gradient(900px 500px at 15% 10%, rgba(199,168,64,.14), transparent 55%),
        radial-gradient(800px 450px at 85% 15%, rgba(138,116,36,.18), transparent 55%),
        linear-gradient(180deg, #082f49 0%, #05273d 55%, #041b2d 100%);">
    <div class="p-4 max-w-8xl mx-auto sm:px-6 lg:px-8 md:py-10">
        <div class="relative overflow-hidden rounded-3xl ee-metal-card">

            {{-- Filete dourado lateral (assinatura visual) --}}
            <span class="absolute left-0 top-0 h-2/5 w-1.5 rounded-e-full ee-gold-edge"></span>

            <div class="grid gap-10 p-6 sm:p-10 lg:grid-cols-2">
                <div>
                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center px-3 py-1 text-xs rounded-full ee-chip">
                            Estilo de Vida
                        </span>
                        <span class="inline-flex items-center px-3 py-1 text-xs rounded-full ee-chip">
                            Saída de Treinamento Prático
                        </span>
                        <span class="inline-flex items-center px-3 py-1 text-xs rounded-full ee-chip">
                            Discipulado e Multiplicação
                        </span>
                    </div>

                    <h1 id="page-title"
                        class="mt-10 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl lg:text-5xl">
                        Evangelismo Eficaz
                    </h1>

                    <p class="mt-4 text-base leading-relaxed text-slate-700 sm:text-lg">
                        Uma jornada ministerial para capacitar a igreja local a compartilhar Cristo com
                        clareza, amor e convicção como estilo de vida.
                    </p>

                    <div class="flex flex-wrap gap-3 mt-14">

                        <x-src.btn-gold label="Treinamento de Líderança" route="#events" />
                        <x-src.btn-silver label="O que é uma Clínica?" route="#clinic" />
                        <x-src.btn-silver label="Treinamento em 5 partes" route="#parts" />

                    </div>

                    <p class="mt-5 text-sm leading-relaxed text-slate-600">
                        No Brasil, o programa é apresentado por módulos com identidade visual própria,
                        preservando a essência do conteúdo do <strong><a href="https://evangelismexplosion.org/"
                                target="_blank">Evangelismo Explosivo Internacional</a></strong>.
                    </p>
                </div>

                <figure class="overflow-hidden shadow-sm rounded-2xl bg-white/60 ring-1 ring-slate-900/10">
                    <img src="{{ asset('images/everyday-evangelism.webp') }}" width="600" height="400"
                        class="object-cover w-full h-full"
                        alt="Equipe de uma igreja em treinamento de evangelismo, reunida para oração e prática"
                        loading="lazy" decoding="async" />
                </figure>
            </div>
        </div>
    </div>
</section>
