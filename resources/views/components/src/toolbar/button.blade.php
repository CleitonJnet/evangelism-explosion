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
    @elseif ($icon === 'calendar-check')
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 7V3m8 4V3M4 11h16M5 7h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V9a2 2 0 012-2z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 15l2 2 4-4" />
        </svg>
    @elseif ($icon === 'home')
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M2.25 12l8.954-8.955a1.125 1.125 0 011.59 0L21.75 12M4.5 9.75v10.125A1.125 1.125 0 005.625 21h3.75a1.125 1.125 0 001.125-1.125V15.75a1.125 1.125 0 011.125-1.125h1.5A1.125 1.125 0 0115.75 15.75v4.125A1.125 1.125 0 0016.875 21h3.75A1.125 1.125 0 0021.75 19.875V9.75" />
        </svg>
    @elseif ($icon === 'arrow-left')
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M10.25 6.75L4.75 12l5.5 5.25M4.75 12h14.5" />
        </svg>
    @elseif ($icon === 'users')
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M18 18.72a9.094 9.094 0 003.741-3.655A3.75 3.75 0 0018 7.5a3.75 3.75 0 00-1.49 7.2M6 18.72a9.094 9.094 0 01-3.741-3.655A3.75 3.75 0 016 7.5a3.75 3.75 0 011.49 7.2M12 15.75a3.75 3.75 0 100-7.5 3.75 3.75 0 000 7.5zm0 0c-3.314 0-6 1.343-6 3v.75h12v-.75c0-1.657-2.686-3-6-3z" />
        </svg>
    @elseif ($icon === 'users-chat')
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <circle cx="7" cy="11" r="2.25" />
            <circle cx="15.5" cy="11" r="2.25" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3.5 19a4 4 0 014-4h2" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M13.25 15h2a4 4 0 014 4" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9.5 4.5h5.5a3 3 0 013 3v1.5a3 3 0 01-3 3H13l-2 2v-2H9.5a3 3 0 01-3-3V7.5a3 3 0 013-3z" />
        </svg>
    @elseif ($icon === 'person-walking')
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 4.5a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9.75 7.5l2.25 1.5-1.5 4.5" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 21l1.5-5.25" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12.75 11.25l4.5 2.25" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15.75 21l-2.25-4.5" />
        </svg>
    @elseif ($icon === 'user-group')
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 20h5v-2a4 4 0 00-5-3.87M9 20H2v-2a4 4 0 015-3.87M16 3.13a4 4 0 010 7.75M8 3.13a4 4 0 000 7.75M12 7a4 4 0 100 8 4 4 0 000-8z" />
        </svg>
    @elseif ($icon === 'document-text')
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19.5 14.25v-2.625A3.375 3.375 0 0016.125 8.25H8.25m0-3.75h2.625A3.375 3.375 0 0114.25 7.875V11.25m-6-6.75H5.625A3.375 3.375 0 002.25 7.875v10.5A3.375 3.375 0 005.625 21.75h8.625A3.375 3.375 0 0017.625 18.375V11.25M8.25 14.25h6.75M8.25 17.25h6.75" />
        </svg>
    @elseif ($icon === 'chart-bar')
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 3v18h18M9 17V9m4 8V5m4 12v-6" />
        </svg>
    @elseif ($icon === 'briefcase')
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 7.5h18M8.25 7.5V6a1.5 1.5 0 011.5-1.5h4.5A1.5 1.5 0 0115.75 6v1.5M6 7.5v11.25A1.5 1.5 0 007.5 20.25h9A1.5 1.5 0 0018 18.75V7.5" />
        </svg>
    @elseif ($icon === 'eye')
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M2.458 12C3.732 7.943 7.523 5.25 12 5.25c4.478 0 8.268 2.693 9.542 6.75-1.274 4.057-5.064 6.75-9.542 6.75-4.477 0-8.268-2.693-9.542-6.75z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
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
