@php
    $sidebarGroupClass = 'grid [&>div>div]:text-slate-300/80';
    $sidebarItemClass =
        'text-slate-100/90 hover:text-white hover:bg-white/10 data-current:text-amber-200 data-current:bg-white/10 data-current:border-amber-200/30 border border-transparent';
@endphp

<flux:sidebar.group :heading="__('Platform')" :class="$sidebarGroupClass">


    @can('access-teacher')
        <flux:sidebar.item icon="layout-grid" :href="route('app.teacher.dashboard')"
            :current="request()->routeIs('app.teacher.dashboard')" wire:navigate :class="$sidebarItemClass">
            <div class="relative"><span class="text-lg">&#10174;</span> {{ __('Dashboard') }}
                <div style="text-shadow: 0 0 1px #000000"
                    class="absolute top-1/2 -translate-1/2 right-0 text-amber-200/90 {{ request()->routeIs('app.teacher.dashboard') ? 'opacity-100' : 'opacity-0' }}">
                    &#10148;
                </div>
            </div>
        </flux:sidebar.item>
        {{-- <flux:sidebar.item :href="route('app.teacher.church.index')" :current="request()->routeIs('app.teacher.church.*')"
            wire:navigate :class="$sidebarItemClass">
            <div class="relative"><span class="text-lg">&#10174;</span> {{ __('Igrejas') }}
                <div style="text-shadow: 0 0 1px #000000"
                    class="absolute top-1/2 -translate-1/2 right-0 text-amber-200/90 {{ request()->routeIs('app.teacher.church.*') ? 'opacity-100' : 'opacity-0' }}">
                    &#10148;
                </div>
            </div>
        </flux:sidebar.item> --}}

        <flux:sidebar.item icon="calendar" :href="route('app.teacher.training.index')"
            :current="request()->routeIs('app.teacher.training.*')" wire:navigate :class="$sidebarItemClass">
            <div class="relative"><span class="text-lg">&#10174;</span> {{ __('Treinamentos') }}
                <div style="text-shadow: 0 0 1px #000000"
                    class="absolute top-1/2 -translate-1/2 right-0 text-amber-200/90 {{ request()->routeIs('app.teacher.training.*') ? 'opacity-100' : 'opacity-0' }}">
                    &#10148;
                </div>
            </div>
        </flux:sidebar.item>

        {{-- <flux:sidebar.item :href="route('app.teacher.inventory.index')"
            :current="request()->routeIs('app.teacher.inventory.*')" wire:navigate :class="$sidebarItemClass">
            <div class="relative"><span class="text-lg">&#10174;</span> {{ __('Estoque') }}
                <div style="text-shadow: 0 0 1px #000000"
                    class="absolute top-1/2 -translate-1/2 right-0 text-amber-200/90 {{ request()->routeIs('app.teacher.inventory.*') ? 'opacity-100' : 'opacity-0' }}">
                    &#10148;
                </div>
            </div>
        </flux:sidebar.item> --}}
    @endcan

</flux:sidebar.group>
