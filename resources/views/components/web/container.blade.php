@props(['display' => true])
<div
    {{ $attributes->merge(['class' => 'relative px-4 py-6 mx-auto border shadow-lg max-w-8xl sm:px-6 lg:px-8 lg:p-8 lg:flex-row xl:rounded-2xl bg-white/95 border-amber-500/25 shadow-black/10']) }}>
    {{ $slot }}

    @if ($display)
        <div aria-hidden="true"
            class="absolute inset-x-0 bottom-0 h-1 pointer-events-none bg-linear-to-r from-transparent via-amber-500 to-transparent">
        </div>
    @endif
</div>
