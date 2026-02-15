@props(['label', 'route', 'current' => false, 'icon' => null, 'iconVariant' => 'outline'])

@php
    $itemClass =
        'h-8 in-data-flux-sidebar-on-mobile:h-10 relative flex items-center gap-3 rounded-lg py-0 text-start w-full px-3 my-px hover:bg-white/10 mt-0.5';
    $stateClass = $current
        ? 'text-amber-200/90 hover:text-amber-100 border border-amber-200/30 bg-white/10'
        : 'text-slate-200/90 hover:text-amber-100 border-0 bg-transparent';
@endphp

<a href="{{ $route }}" wire:navigate data-flux-sidebar-item @if ($current) data-current @endif
    {{ $attributes->class($itemClass . ' ' . $stateClass) }}>
    @if ($icon)
        <flux:icon :icon="$icon" :variant="$iconVariant"
            class="size-4 [[data-flux-sidebar-item]:hover_&]:text-current!" />
    @endif

    <div class="relative flex-1 truncate text-sm font-medium">
        {{-- <span class="text-lg">&#10174;</span> --}}
        {{ __($label) }}
        <div style="text-shadow: 0 0 1px #000000"
            class="absolute top-1/2 right-0 -translate-y-1/2 text-amber-200/90 {{ $current ? 'opacity-100' : 'opacity-0' }}">
            &#10148;
        </div>
    </div>
</a>
