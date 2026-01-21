@props(['label', 'route' => null])

@once
    @push('css')
        <style>
            /* Botão primário: ouro */
            .ee-btn-gold {
                position: relative;
                overflow: hidden;

                /* MESMO gradiente do botão do menu */
                background: linear-gradient(135deg, #f1d57a, #c7a840, #8a7424);
                border: 1px solid rgba(255, 255, 255, .20);
                color: #1b1709;

                box-shadow:
                    0 2px 2px rgba(0, 0, 0, .22),
                    inset 0 1px 0 rgba(255, 255, 255, .45);

                transition: filter .18s ease, transform .18s ease, box-shadow .18s ease;
                text-shadow: 1px 1px 2px rgba(255, 255, 255, .9);
            }

            .ee-btn-gold:hover {
                filter: brightness(1.10);
                transform: translateY(-1px);
                box-shadow:
                    0 4px 5px rgba(0, 0, 0, .26),
                    inset 0 1px 0 rgba(255, 255, 255, .55);
            }
        </style>
    @endpush
@endonce

@if ($route)
    <a href="{{ $route }}" title="{!! $label ?? '' !!}"
        {{ $attributes->merge(['class' => 'inline-flex items-center justify-center px-6 py-3 text-sm font-bold rounded-xl cursor-pointer shine ee-btn-gold focus:outline-none focus-visible:ring-4 focus-visible:ring-amber-300/40']) }}>
        {!! $label ?? '' !!} {!! $slot !!}
    </a>
@else
    <button type="button" title="{!! $label ?? '' !!}"
        {{ $attributes->merge(['class' => 'inline-flex items-center justify-center px-6 py-3 text-sm font-bold rounded-xl cursor-pointer shine ee-btn-gold focus:outline-none focus-visible:ring-4 focus-visible:ring-amber-300/40']) }}>
        {!! $label ?? '' !!} {!! $slot !!}
    </button>
@endif
