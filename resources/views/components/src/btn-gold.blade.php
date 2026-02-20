@props(['label', 'route' => null, 'type' => 'button'])

@php
    $classes =
        'inline-flex items-center justify-center px-6 py-3 text-sm font-bold rounded-xl cursor-pointer shine ee-btn-gold focus:outline-none focus-visible:ring-4 focus-visible:ring-amber-300/40';
@endphp

@if ($route)
    <a href="{{ $route }}" title="{!! $label ?? '' !!}" {{ $attributes->merge(['class' => $classes]) }}>
        {!! $label ?? '' !!} {!! $slot !!}
    </a>
@else
    <button type="{{ $type }}" title="{!! $label ?? '' !!}" {{ $attributes->merge(['class' => $classes]) }}>
        {!! $label ?? '' !!} {!! $slot !!}
    </button>
@endif
