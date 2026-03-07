@props([
    'label' => null,
    'key' => null,
    'wrapperClass' => 'bg-slate-200 hover:bg-sky-200 border-slate-300',
    'trackClass' => 'bg-white peer-checked:bg-sky-950 border-slate-400/70',
    'thumbClass' => 'bg-amber-400',
])

@php
    $switchId = \Illuminate\Support\Str::slug(trim((string) $label) . '-' . trim((string) $key));
@endphp

<label for="{{ $switchId }}"
    class="flex flex-col items-center justify-center gap-1 rounded-xl transition duration-200 basis-20 py-1.5 cursor-pointer border px-4 {{ $wrapperClass }}">
    <div class="relative inline-flex items-center">
        <input type="checkbox" id="{{ $switchId }}"
            {{ $attributes->merge(['class' => 'peer sr-only focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0']) }} />
        <span class="h-5 w-9 rounded-full transition border {{ $trackClass }}"
            style="box-shadow: inset 0 0 8px 0 rgba(0,0,0,0.4)"></span>
        <span class="absolute left-1 top-1 h-3 w-3 rounded-full transition peer-checked:translate-x-4 {{ $thumbClass }}"></span>
    </div>
    <div class="truncate text-xs">{{ $label }}</div>
</label>
