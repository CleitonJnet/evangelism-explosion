@props(['title' => '', 'subtitle' => null, 'cover' => asset('images/bg_welcome/photo1.webp')])

<header
    {{ $attributes->merge(['class' => 'relative py-16 overflow-hidden bg-center bg-no-repeat bg-cover after:absolute after:inset-0 after:bg-sky-950/85']) }}
    style="background-image: url({{ $cover }});">

    <div class="relative z-10 flex flex-col items-center gap-4 px-4 mx-auto max-w-8xl mx-auto sm:px-6 lg:px-8 pt-28">

        <h1 class="max-w-xl text-3xl text-center text-amber-300 sm:text-4xl" style="font-family: 'Cinzel', serif;">
            {!! $title !!}
        </h1>

        @if ($subtitle)
            <p class="max-w-4xl text-lg text-center text-white/90">
                {!! $subtitle !!}
            </p>
        @endif
    </div>

    <div class="absolute inset-x-0 bottom-0 h-2 z-10 bg-linear-to-r from-[#f1d57a] via-[#c7a840] to-[#8a7424]"></div>
</header>
