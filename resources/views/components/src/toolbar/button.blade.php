@props([
    'href' => '#',
    'label' => '',
    'icon' => 'calendar',
    'active' => false,
    'tooltip' => null,
])

@php
    $isActive = filter_var($active, FILTER_VALIDATE_BOOL);
    $tooltipText = $tooltip ?? $label;
    $baseStyles = $isActive
        ? 'background:#082f49;color:#f1d57a;border:1px solid #c7a840;'
        : 'background:rgba(2,6,23,0.04);color:#0f172a;border:1px solid rgba(15,23,42,0.1);';
@endphp

<a {{ $attributes->merge([
    'href' => $href,
    'aria-label' => $label,
    'style' => $baseStyles,
])->class('group relative flex w-20 flex-col items-center justify-center gap-1 rounded-xl px-3 py-2 text-xs font-semibold transition') }}>
    <span
        class="absolute -top-8 left-1/2 z-10 hidden -translate-x-1/2 whitespace-nowrap rounded-md bg-slate-900 px-2 py-1 text-xs text-amber-100 shadow-lg group-hover:block">
        {{ $tooltipText }}
    </span>

    @if ($icon === 'list')
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 5h10M9 12h10M9 19h10M5 5h.01M5 12h.01M5 19h.01" />
        </svg>
    @elseif ($icon === 'calendar')
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 7V3m8 4V3M4 11h16M5 7h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V9a2 2 0 012-2z" />
        </svg>
    @elseif ($icon === 'x')
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    @elseif ($icon === 'check')
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
    @elseif ($icon === 'plus')
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
    @elseif ($icon === 'pencil')
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15.232 5.232l3.536 3.536M4 20h4.586a1 1 0 00.707-.293l9.914-9.914a1 1 0 000-1.414l-3.586-3.586a1 1 0 00-1.414 0L4.586 15.414A1 1 0 004 16.121V20z" />
        </svg>
    @elseif ($icon === 'trash')
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-7 0V5a2 2 0 012-2h2a2 2 0 012 2v2" />
        </svg>
    @elseif ($icon === 'hourglass')
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M6 2h12M6 22h12M8 2v4a4 4 0 001.172 2.828L12 11l2.828-2.172A4 4 0 0016 6V2M8 22v-4a4 4 0 011.172-2.828L12 13l2.828 2.172A4 4 0 0116 18v4" />
        </svg>
    @endif

    <span class="w-full truncate text-[9px] text-center uppercase">{{ $label }}</span>
</a>
