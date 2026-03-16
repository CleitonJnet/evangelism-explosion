@props([
    'label',
    'value',
    'hint' => null,
    'tone' => 'neutral',
])

@php
    $toneClasses = match ($tone) {
        'sky' => 'border-sky-200 bg-sky-50 text-sky-950',
        'amber' => 'border-amber-200 bg-amber-50 text-amber-950',
        'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-950',
        default => 'border-neutral-200 bg-neutral-50 text-neutral-950',
    };
@endphp

<div {{ $attributes->class("rounded-2xl border p-5 shadow-sm {$toneClasses}") }}>
    <div class="flex flex-col gap-2">
        <span class="text-xs font-semibold uppercase tracking-[0.2em] text-neutral-500">{{ $label }}</span>
        <span class="text-3xl font-semibold">{{ $value }}</span>

        @if ($hint)
            <p class="text-sm text-neutral-600">{{ $hint }}</p>
        @endif
    </div>
</div>
