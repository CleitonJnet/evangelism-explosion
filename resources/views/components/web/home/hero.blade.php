@push('css')
    <style>
        /* =========================================================
                                                                                HERO MISSION — Evangelismo Explosivo no Brasil
                                                                                ========================================================= */

        /* ---------- Variáveis Globais ---------- */
        :root {
            --txt-duration-animation: 2900ms;

            --bg-fade-duration: 1400ms;
            --bg-scale: 1.05;
            --bg-pan-duration: 30s;

            --cta-gap: 14px;
            --cta-radius: 14px;

            /* Paleta da logo (ouro + prata) */
            --ee-gold-1: #C9B359;
            --ee-gold-2: #9C8F44;
            --ee-gold-3: #69662D;

            --ee-silver-1: #E1E3E6;
            --ee-silver-2: #B9BABD;
            --ee-silver-3: #989A9C;

            --ee-ink: #0f172a;
        }

        /* ---------- max-w-8xl mx-auto do Hero ---------- */
        .hero-mission {
            position: relative;
            height: 100dvh;
            overflow: hidden;
            background-color: #0f172a;
            display: grid;
            place-items: center;
            isolation: isolate;
        }

        /* ---------- Background (crossfade em 2 camadas) ---------- */
        .hero-bg {
            position: absolute;
            inset: -6%;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;

            opacity: 0;
            z-index: 0;

            transform-origin: center;
            will-change: opacity, transform;

            transition: opacity var(--bg-fade-duration) ease-in-out;

            transform: scale(var(--bg-scale));
            animation: bgPan var(--bg-pan-duration) linear infinite;
        }

        .hero-bg.is-active {
            opacity: 1;
        }

        @keyframes bgPan {
            0% {
                transform: translateX(-2%) scale(var(--bg-scale));
            }

            50% {
                transform: translateX(2%) scale(var(--bg-scale));
            }

            100% {
                transform: translateX(-2%) scale(var(--bg-scale));
            }
        }

        /* ---------- Overlay ---------- */
        .hero-mission::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom,
                    rgba(15, 23, 42, .30),
                    rgba(15, 23, 42, .25),
                    rgba(15, 23, 42, .30));
            z-index: 1;
        }

        /* ---------- Texto ---------- */
        .text-title {
            position: relative;
            z-index: 2;
            width: 100%;
            height: 100%;
            display: grid;
            place-items: center;
            padding: 24px;

            text-shadow:
                #000 1px -3px,
                #000 -3px 1px,
                #000 3px 3px,
                #000 -3px -3px;
        }

        .text-title .bloco {
            position: absolute;
            inset: 0;
            display: grid;
            place-items: center;
            opacity: 0;
            pointer-events: none;
            transition: opacity 350ms ease;
        }

        .text-title .bloco.is-active {
            opacity: 1;
            pointer-events: auto;
        }

        .stack {
            font-family: "Tilt Warp", sans-serif;
            display: grid;
            grid-template-rows: auto auto auto;
            row-gap: clamp(14px, 3.5vw, 42px);
            width: min(92vw, 1100px);
            text-align: center;
        }

        .todo {
            font-size: clamp(3.8rem, 9vw, 7rem);
            color: #ffcf0d;
            line-height: 2rem;
        }

        .text-aux {
            font-size: clamp(2.6rem, 5.8vw, 4rem);
            color: #ffffff;
        }

        /* Animações de entrada */
        .frase-1 {
            transform: translateY(-40px);
            opacity: 0;
        }

        .frase-2 {
            transform: rotateY(-90deg);
            opacity: .4;
        }

        .frase-3 {
            transform: translateY(40px);
            opacity: 0;
        }

        .bloco.is-active .frase-1,
        .bloco.is-active .frase-2,
        .bloco.is-active .frase-3 {
            transform: none;
            opacity: 1;
            transition: all var(--txt-duration-animation) ease;
        }

        /* ---------- CTAs Fixos ---------- */
        .hero-cta-fixed {
            position: absolute;
            z-index: 3;
            left: 50%;
            bottom: clamp(18px, 4vh, 42px);
            transform: translateX(-50%);
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: var(--cta-gap);
            width: min(92vw, 960px);
            padding: 0 16px;
        }

        /* Botões */
        .btn-cta {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            text-align: center;
            text-shadow: 1px 1px 4px #000000;
            backdrop-filter: blur(10px);

            border-radius: var(--cta-radius);
            min-height: 66px;
            padding: 14px;

            border: 1px solid rgba(185, 186, 189, .6);
            background: linear-gradient(180deg,
                    rgba(225, 227, 230, .18),
                    rgba(152, 154, 156, .10));

            color: #fff;
            font-weight: 800;
            box-shadow: 0 12px 28px rgba(0, 0, 0, .28);
            transition: all 180ms ease;
        }

        .btn-cta:hover {
            transform: translateY(-2px);
            backdrop-filter: blur(15px);

        }

        .btn-sub {
            margin-top: 4px;
            font-size: .9rem;
            opacity: .9;
        }

        /* CTA principal — Ouro */
        .btn-primary {
            color: var(--ee-ink);
            background: linear-gradient(135deg,
                    var(--ee-gold-1),
                    #E6D589,
                    var(--ee-gold-2),
                    var(--ee-gold-1));
            border-color: var(--ee-gold-1);
            text-shadow: 1px 1px 5px rgba(255, 255, 255, .5);
        }

        @media (max-width: 800px) {
            .hero-cta-fixed {
                grid-template-columns: repeat(2, 1fr);
            }

            .cta-optional {
                display: none !important;
            }
        }

        @media (max-width: 500px) {
            .btn-cta {
                padding: 8px;
            }

            .hero-cta-fixed {
                grid-template-columns: repeat(1, 1fr);
            }

            .btn-sub {
                display: none !important;
            }
        }
    </style>
@endpush

<section class="hero-mission">

    <div class="hero-bg is-active"></div>
    <div class="hero-bg"></div>

    <div class="text-title">

        <div class="bloco">
            <div class="stack">
                <div class="frase-1 text-aux"></div>
                <div class="frase-2 todo">CADA</div>
                <div class="frase-3 text-aux">NAÇÃO</div>
            </div>
        </div>

        <div class="bloco">
            <div class="stack">
                <div class="frase-1 text-aux">CAPACITANDO</div>
                <div class="frase-2 todo">CADA</div>
                <div class="frase-3 text-aux">GRUPO ÉTNICO</div>
            </div>
        </div>

        <div class="bloco">
            <div class="stack">
                <div class="frase-1 text-aux">E</div>
                <div class="frase-2 todo">CADA</div>
                <div class="frase-3 text-aux">FAIXA ETÁRIA</div>
            </div>
        </div>

        <div class="bloco">
            <div class="stack">
                <div class="frase-1 text-aux">A TESTEMUNHAR A</div>
                <div class="frase-2 todo">CADA</div>
                <div class="frase-3 text-aux">PESSOA.</div>
            </div>
        </div>

    </div>

    <div class="hero-cta-fixed">
        <a href="{{ route('web.ministry.kids-ee') }}" class="btn-cta btn-primary shine">
            EE-Kids
            <span class="btn-sub">Evangelismo para crianças</span>
        </a>

        <a href="{{ route('web.ministry.everyday-evangelism') }}" class="btn-cta shine">
            Evangelismo Eficaz
            <span class="btn-sub">Treinamento prático</span>
        </a>

        <a href="{{ route('web.event.schedule') }}" class="btn-cta cta-optional shine">
            Agendar
            <span class="btn-sub">Evento de Lançamento</span>
        </a>
    </div>

</section>

@push('js')
    <script>
        (() => {
            const root = document.querySelector('.hero-mission');
            if (!root) return;

            const blocks = [...root.querySelectorAll('.bloco')];
            const layers = [...root.querySelectorAll('.hero-bg')];

            const BACKGROUNDS = [
                '{{ asset('images/hero/photo1.webp') }}',
                '{{ asset('images/hero/photo2.webp') }}',
                '{{ asset('images/hero/photo3.webp') }}',
                '{{ asset('images/hero/photo4.webp') }}',
            ];

            const TEXT_DURATION = 4500;

            let index = 0;
            let activeLayer = 0;
            let timer = null;

            BACKGROUNDS.forEach(src => {
                const i = new Image();
                i.src = src;
            });

            function render(i) {
                blocks.forEach(b => b.classList.remove('is-active'));
                blocks[i].classList.add('is-active');

                const next = 1 - activeLayer;
                layers[next].style.backgroundImage = `url(${BACKGROUNDS[i]})`;
                layers[next].classList.add('is-active');
                layers[activeLayer].classList.remove('is-active');
                activeLayer = next;
            }

            function reset() {
                clearInterval(timer);
                index = 0;
                activeLayer = 0;
                render(0);
            }

            function start() {
                reset();
                timer = setInterval(() => {
                    index = (index + 1) % blocks.length;
                    render(index);
                }, TEXT_DURATION);
            }

            document.addEventListener('visibilitychange', () => {
                document.hidden ? reset() : start();
            });

            start();
        })();
    </script>
@endpush
