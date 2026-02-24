<div class="relative flex flex-col mb-10 overflow-hidden md:flex-row rounded-3xl ring-1 ring-slate-900/10 bg-linear-to-r from-white via-white to-blue-50"
    style="box-shadow: -25px 1px 5px -25px #2e3192 inset, 25px 1px 5px -25px #2e3192 inset, 1px -25px 2px -25px #2e3192 inset;
-webkit-box-shadow: -25px 1px 5px -25px #2e3192 inset, 25px 1px 5px -25px #2e3192 inset, 1px -25px 2px -25px #2e3192 inset;
-moz-box-shadow: -25px 1px 5px -25px #2e3192 inset, 25px 1px 5px -25px #2e3192 inset, 1px -25px 2px -25px #2e3192 inset;">
    {{-- Capa (sem recorte) --}}
    @if (file_exists(public_path('images/cover/syfw.jpg')))
        <div class="w-full mx-auto md:w-1/3 lg:w-1/4 xl:w-1/5">
            {{-- “palco” da imagem: controla altura e mantém a capa completa --}}
            <div class="flex items-center justify-center p-4 md:p-6 h-[260px] md:h-full">
                <img src="{{ asset('images/cover/syfw.jpg') }}" alt="Workshop O Evangelho Em Sua Mão"
                    class="object-contain w-auto max-w-full max-h-full rounded-lg drop-shadow-sm" loading="lazy"
                    decoding="async" />
            </div>
        </div>
    @endif

    {{-- Conteúdo --}}
    <div class="relative w-full px-8 py-12">

        {{-- Selo --}}
        <div class="absolute px-3 py-1 text-xs font-black text-white uppercase top-5 left-7 rounded-xl"
            style="
                text-shadow: 1px 1px 2px rgba(0,0,0,0.75);
                background-color: #2e3192;
            ">
            Parte 1: Entenda
        </div>

        {{-- Título --}}
        <h3 class="pt-6 text-2xl font-extrabold text-sky-950">
            Workshop O Evangelho Em Sua Mão
        </h3>

        {{-- Subtítulo --}}
        <p class="mt-2 text-sm font-semibold tracking-wide uppercase" style="color: #2e3192;">
            Treinamento introdutório em evangelismo pessoal
        </p>

        {{-- Texto principal --}}
        <p class="mt-4 text-base leading-relaxed text-sky-950">
            O <strong>Workshop O Evangelho Em Sua Mão (ESM)</strong> é a porta de entrada
            do Evangelismo Explosivo. Trata-se de um treinamento prático que ensina,
            de forma clara e motivadora, como compartilhar o Evangelho utilizando
            ilustrações bíblicas e respondendo às principais objeções.
        </p>

        <p class="mt-3 text-base leading-relaxed text-sky-950">
            O workshop pode ser agendado isoladamente pela igreja, preferencialmente
            em um único dia, normalmente aos <strong>sábados, das 9h às 17h,</strong>
            ou em blocos sequenciais, sem perder o ritmo do treinamento. Pode ser
            ministrado por um <strong>professor credenciado do EE-Brasil</strong>
            ou por instrutores locais devidamente treinados.
        </p>

        <div class="flex justify-end mt-4">
            <x-src.btn-silver label="Veja como agendar um Workshop ESM" route="{{ route('web.event.clinic-base') }}" />
        </div>

        {{-- Linha decorativa --}}
        <div class="mt-6 h-0.75 w-1/4" style="background-color: #2e3192; border-start-end-radius: 100%;"></div>

        <p
            class="px-3 py-2 text-sm italic font-bold rounded-b-2xl text-yellow-950 bg-linear-to-r from-yellow-300 to-yellow-100/5">
            Todo movimento começa com compreensão — e o Workshop ESM dá o primeiro passo.
        </p>

    </div>
</div>
