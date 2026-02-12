@php
    $sidebarGroupClass = 'grid [&>div>div]:text-slate-300/80';
    $sidebarItemClass =
        'text-slate-100/95 hover:text-amber-100 hover:bg-white/15 data-current:text-amber-200 data-current:bg-white/12 data-current:border-amber-200/30 border border-transparent';
@endphp

<flux:sidebar.group :heading="__('Plataforma')" :class="$sidebarGroupClass">


    @can('access-director')
        <flux:sidebar.item :href="route('app.director.dashboard')" :current="request()->routeIs('app.director.dashboard')"
            wire:navigate :class="$sidebarItemClass">
            <div class="relative"><span class="text-lg">&#10174;</span> {{ __('Painel de Controle') }}
                <div style="text-shadow: 0 0 1px #000000"
                    class="absolute top-1/2 -translate-1/2 right-0 text-amber-200/90 {{ request()->routeIs('app.director.dashboard') ? 'opacity-100' : 'opacity-0' }}">
                    &#10148;
                </div>
            </div>
        </flux:sidebar.item>

        <flux:sidebar.item :href="route('app.director.church.index')" :current="request()->routeIs('app.director.church.*')"
            wire:navigate :class="$sidebarItemClass">
            <div class="relative"><span class="text-lg">&#10174;</span> {{ __('Igrejas') }}
                <div style="text-shadow: 0 0 1px #000000"
                    class="absolute top-1/2 -translate-1/2 right-0 text-amber-200/90 {{ request()->routeIs('app.director.church.*') ? 'opacity-100' : 'opacity-0' }}">
                    &#10148;
                </div>
            </div>
        </flux:sidebar.item>

        <flux:sidebar.item :href="route('app.director.ministry.index')"
            :current="request()->routeIs('app.director.ministry.*')" wire:navigate :class="$sidebarItemClass">
            <div class="relative"><span class="text-lg">&#10174;</span> {{ __('Ministérios') }}
                <div style="text-shadow: 0 0 1px #000000"
                    class="absolute top-1/2 -translate-1/2 right-0 text-amber-200/90 {{ request()->routeIs('app.director.ministry.*') ? 'opacity-100' : 'opacity-0' }}">
                    &#10148;
                </div>
            </div>
        </flux:sidebar.item>

        <flux:sidebar.item :href="route('app.director.training.index')"
            :current="request()->routeIs('app.director.training.*')" wire:navigate :class="$sidebarItemClass">
            <div class="relative"><span class="text-lg">&#10174;</span> {{ __('Treinamentos') }}
                <div style="text-shadow: 0 0 1px #000000"
                    class="absolute top-1/2 -translate-1/2 right-0 text-amber-200/90 {{ request()->routeIs('app.director.training.*') ? 'opacity-100' : 'opacity-0' }}">
                    &#10148;
                </div>
            </div>
        </flux:sidebar.item>

        <flux:sidebar.item :href="route('app.director.inventory.index')"
            :current="request()->routeIs('app.director.inventory.*')" wire:navigate :class="$sidebarItemClass">
            <div class="relative"><span class="text-lg">&#10174;</span> {{ __('Estoque') }}
                <div style="text-shadow: 0 0 1px #000000"
                    class="absolute top-1/2 -translate-1/2 right-0 text-amber-200/90 {{ request()->routeIs('app.director.inventory.*') ? 'opacity-100' : 'opacity-0' }}">
                    &#10148;
                </div>
            </div>
        </flux:sidebar.item>
    @endcan

</flux:sidebar.group>
