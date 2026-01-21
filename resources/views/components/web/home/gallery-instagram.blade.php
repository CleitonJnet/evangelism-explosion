@push('css')
    <style>
        /* Mantém EXATAMENTE o “coração” do efeito: ::before + conic-gradient + animação */
        .ee-gold-animated-border::before {
            content: "";
            position: absolute;
            inset: -140%;
            background: conic-gradient(from 180deg,
                    #fff3c4,
                    #f1d57a,
                    #c7a840,
                    #8a7424,
                    #c7a840,
                    #f1d57a,
                    #fff3c4);
            animation: eeGoldSpin 5s linear infinite;
            opacity: .9;
        }

        @keyframes eeGoldSpin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
@endpush


<section class="relative max-w-8xl mx-auto rounded-2xl overflow-hidden">

    {{-- brilho dourado suave (mantido) --}}
    <div
        class="absolute inset-0 pointer-events-none
                bg-[radial-gradient(circle_at_30%_15%,rgba(199,168,64,0.16),transparent_55%)]">
    </div>

    {{-- ee-gold-animated-border (mesma estrutura/efeito) --}}
    <div class="relative ee-gold-animated-border 2xl:rounded-xl p-0.5 overflow-hidden">

        {{-- ee-gold-inner (convertido fielmente) --}}
        <div
            class="relative rounded-2xl
                    bg-white/95 backdrop-blur-[10px]
                    border border-amber-500/25
                    px-6 py-8 sm:px-8 sm:py-10">

            {{-- Título --}}
            <div class="mb-6 text-center">
                <h3 class="text-2xl sm:text-3xl lg:text-4xl text-slate-900"
                    style="font-family:'Cinzel', serif; text-shadow:1px 1px 10px rgba(199,168,64,.35);">
                    Nosso Instagram
                </h3>

                <p class="max-w-xl mx-auto mt-3 text-sm sm:text-base text-slate-600">
                    Treinamentos em diferentes regiões do Brasil.
                </p>
            </div>

            {{-- Linha temática --}}
            <div class="mb-6 mt-4 h-0.5 w-full mx-auto lg:mx-0"
                style="border-radius: 100%; background: linear-gradient(135deg,
                        #c7a8401a,
                        #c7a8408c,
                        #c7a8401a);">
            </div>


            {{-- LightWidget (API) — NÃO ALTERADO --}}
            <script src="https://cdn.lightwidget.com/widgets/lightwidget.js"></script>
            <iframe src="//lightwidget.com/widgets/ca62dde7f5135bb29aba64c1686f2fd3.html" scrolling="no"
                allowtransparency="true"
                class="lightwidget-widget w-full overflow-hidden rounded-2xl bg-white border-0">
            </iframe>

        </div>
    </div>
</section>
