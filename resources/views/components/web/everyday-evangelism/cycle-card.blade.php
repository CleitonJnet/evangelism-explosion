@props([
    'part',
    'title',
    'code',
    'color',
    'cover',
    'logo',
    'text',
    'side' => 'left', // left|right (só para alternar o “clima” visual)
])

<div class="relative p-6 text-center border-t card-ev2 sm:p-10 md:border-t-0 md:border-l border-slate-200 lg:text-left"
    style="background: {{ $color }};
        background: linear-gradient(90deg, {{ $color }}05, {{ $color }}08, {{ $color }}50);">

    {{-- Faixa temática no rodapé do quadrante (como na imagem) --}}
    <div class="absolute inset-x-0 bottom-0 h-2" style="background-color: {{ $color }};"></div>

    {{-- Conteúdo em 2 colunas --}}
    <div class="flex flex-col items-center gap-6 lg:flex-row lg:gap-8">

        {{-- Texto --}}
        <div class="flex-1 order-2 min-w-0 lg:order-1">

            {{-- selo de código --}}
            <div class="absolute px-3 py-1 text-xs font-black text-white uppercase top-5 left-5 rounded-xl"
                style="text-shadow: 1px 1px 2px rgba(0,0,0,0.75); background-color: {{ $color }};">
                {{ $part }}:
            </div>

            <div class="flex flex-col items-center gap-3 mt-4 lg:flex-row">
                {{-- logo pequena (canto inferior) --}}
                @if (!empty(asset($logo)))
                    <img src="{{ asset($logo) }}" alt="Logo {{ $code }}" class="object-contain h-14">
                @endif

                <h3 class="mt-1 text-xl font-extrabold leading-snug xl:text-2xl sm:text-xl text-slate-800">
                    {!! $title !!}
                </h3>

            </div>

            {{-- Linha temática --}}
            <div class="mt-4 h-[2px] w-full mx-auto lg:mx-0"
                style="border-radius: 100%; background: linear-gradient(135deg,
                        {{ $color }}1a,
                        {{ $color }}8c,
                        {{ $color }}1a);">
            </div>
            {{-- background: {{ $color }};  --}}
            <p class="mt-4 text-sm leading-relaxed sm:text-base text-sky-950">
                {{ $text }}
            </p>
        </div>

        @if (file_exists(public_path($cover)))
            {{-- Visual (capa do manual) --}}
            <img src="{{ asset($cover) }}" alt="{{ $code }}"
                class="order-1 object-contain h-full bg-white rounded-lg shadow max-w-40 ring-1 ring-slate-900/10 lg:order-2"
                loading="lazy" decoding="async" />
        @endif

    </div>
</div>
