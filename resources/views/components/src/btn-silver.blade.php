@props(['label', 'route' => null])

@once
    @push('css')
        <style>
            /* Botão secundário: prata nobre */
            .ee-btn-silver {
                background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 45%, #e2e8f0 100%);
                color: #0f172a;
                text-shadow: 1px 1px 2px rgba(255, 255, 255, 1);
                border: 1px solid rgba(15, 23, 42, .22);
                box-shadow:
                    inset 0 1px 0 rgba(255, 255, 255, .75),
                    0 2px 2px rgba(0, 0, 0, .12);
                transition: background .18s ease, transform .18s ease, box-shadow .18s ease;
            }

            .ee-btn-silver:hover {
                background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
                transform: translateY(-1px);
                box-shadow:
                    inset 0 1px 0 rgba(255, 255, 255, .80),
                    0 4px 5px rgba(0, 0, 0, .14);
            }
        </style>
    @endpush
@endonce

@if ($route)
    <a href="{{ $route }}" title="{!! $label ?? '' !!}"
        {{ $attributes->merge(['class' => 'inline-flex items-center justify-center px-6 py-3 text-sm font-bold rounded-xl cursor-pointer shine ee-btn-silver focus:outline-none focus-visible:ring-4 focus-visible:ring-amber-300/40']) }}>
        {!! $label ?? '' !!} {!! $slot !!}
    </a>
@else
    <button type="button" title="{!! $label ?? '' !!}"
        {{ $attributes->merge(['class' => 'inline-flex items-center justify-center px-6 py-3 text-sm font-bold rounded-xl cursor-pointer shine ee-btn-silver focus:outline-none focus-visible:ring-4 focus-visible:ring-amber-300/40']) }}>
        {!! $label ?? '' !!} {!! $slot !!}
    </button>
@endif
