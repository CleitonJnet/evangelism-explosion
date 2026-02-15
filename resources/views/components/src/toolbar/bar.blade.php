@props(['justify' => 'justify-start'])

<div
    {{ $attributes->merge(['class' => 'rounded-2xl border-x border-b border-amber-300/20 bg-linear-to-br from-white via-slate-50 to-slate-200 px-5 py-2 shadow-sm mb-4 w-full sticky top-0']) }}>

    <div class="flex flex-wrap items-center justify-between gap-4 w-full">
        <div class="w-full flex flex-wrap items-center gap-2 text-sm text-slate-700 {{ $justify }}">
            {{ $slot }}
        </div>
    </div>
</div>
