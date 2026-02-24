@props([
    'href' => '#',
    'label' => '',
    'tooltip' => null,
    'active' => false,
])

@php
    $isActive = filter_var($active, FILTER_VALIDATE_BOOL);
    $tooltipText = $tooltip ?? $label;
    $baseStyles = $isActive
        ? 'background:#7c2d12;color:#fde68a;border:1px solid #f59e0b;'
        : 'background:rgba(251,191,36,0.12);color:#7c2d12;border:1px solid rgba(245,158,11,0.45);';
@endphp

<a href="{{ $href }}"
    class="group relative flex w-20 flex-col items-center justify-center gap-1 rounded-xl px-3 py-2 text-xs font-semibold transition"
    aria-label="{{ $label }}" style="{{ $baseStyles }}">
    <span
        class="pointer-events-none absolute -top-6 left-1/2 z-[1300] hidden -translate-x-1/2 whitespace-nowrap rounded-md bg-slate-900 px-2 py-1 text-xs text-amber-100 shadow-lg group-hover:block">
        {{ $tooltipText }}
    </span>

    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 2a10 10 0 100 20 10 10 0 000-20z" />
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M2 12h20M12 2c2.5 2.7 4 6.1 4 10s-1.5 7.3-4 10c-2.5-2.7-4-6.1-4-10s1.5-7.3 4-10z" />
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 7.5h15M4.5 16.5h15" />
    </svg>

    <span class="w-full truncate text-[11px] text-center">{{ $label }}</span>
</a>
