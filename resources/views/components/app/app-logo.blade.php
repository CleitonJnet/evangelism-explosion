@props([
    'sidebar' => false,
])

@if ($sidebar)
    <a
        {{ $attributes->merge(['class' => 'flex items-center h-full gap-3 in-data-flux-sidebar-collapsed-desktop:justify-center']) }}>
        <img src="{{ asset('images/logo/ee-white.webp') }}" class="w-auto h-10 nav-iconshadow"
            alt="{{ __('Evangelismo Explosivo') }}">
        <div class="leading-tight in-data-flux-sidebar-collapsed-desktop:hidden">
            <div class="relative text-xs text-white nav-cinzel">
                EVANGELISMO EXPLOSIVO
            </div>
            <div
                class="hidden min-[345px]:flex min-[345px]:items-center min-[345px]:gap-1 text-xs font-bold bg-linear-to-r from-[#f5e6a8] via-[#d6b85f] to-[#b89b3c] bg-clip-text text-transparent tracking-wide truncate">
                NO BRASIL <span style="font-size: 9px;">&#10023;</span>
            </div>
        </div>
    </a>
@else
    <flux:brand name="Evangelism Explosion" {{ $attributes }}>
        <x-slot name="logo"
            class="flex aspect-square size-8 items-center justify-center rounded-md text-accent-foreground">
            <x-app.app-logo-icon class="size-5 fill-current text-white dark:text-black" />
        </x-slot>
    </flux:brand>
@endif
