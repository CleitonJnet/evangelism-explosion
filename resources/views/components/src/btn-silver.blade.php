@props(['label', 'route' => null])

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
