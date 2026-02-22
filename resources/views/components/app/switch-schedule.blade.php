@props(['label' => null, 'key' => null])

@php
    $switchId = \Illuminate\Support\Str::slug(trim((string) $label) . '-' . trim((string) $key));
@endphp

<label for="{{ $switchId }}"
    class="flex flex-col items-center justify-center gap-1 rounded-xl bg-slate-200 hover:bg-sky-200 transition duration-200 basis-20 py-1.5 cursor-pointer border border-slate-300 px-4">
    <div class="relative inline-flex items-center">
        <input type="checkbox" id="{{ $switchId }}"
            {{ $attributes->merge(['class' => 'peer sr-only focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0']) }} />
        <span class="h-5 w-9 rounded-full bg-white transition peer-checked:bg-sky-950 border border-slate-400/70"
            style="box-shadow: inset 0 0 8px 0 rgba(0,0,0,0.4)"></span>
        <span
            class="absolute left-1 top-1 h-3 w-3 rounded-full bg-amber-400 transition peer-checked:translate-x-4"></span>
    </div>
    <div class="truncate text-xs">{{ $label }}</div>
</label>
