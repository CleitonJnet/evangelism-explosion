@php
    $sidebarGroupClass = 'grid [&>div>div]:truncate [&>div>div]:text-slate-300/80';
    $sidebarItemClass =
        'text-slate-100/90 hover:text-white hover:bg-white/10 data-current:text-amber-200 data-current:bg-white/10 data-current:border-amber-200/30 border border-transparent';
@endphp

<flux:sidebar.group :heading="__('Plataforma do Mentor')" :class="$sidebarGroupClass">


    @can('access-mentor')
        <flux:sidebar.item icon="layout-grid" :href="route('app.mentor.dashboard')"
            :current="request()->routeIs('app.mentor.dashboard')" wire:navigate :class="$sidebarItemClass">
            <div class="relative"><span class="text-lg">&#10174;</span> {{ __('Dashboard') }}
                <div style="text-shadow: 0 0 1px #000000"
                    class="absolute top-1/2 -translate-1/2 right-0 text-amber-200/90 {{ request()->routeIs('app.mentor.dashboard') ? 'opacity-100' : 'opacity-0' }}">
                    &#10148;
                </div>
            </div>
        </flux:sidebar.item>

        <flux:sidebar.item icon="calendar" :href="route('app.mentor.trainings.index')"
            :current="request()->routeIs('app.mentor.trainings.*')" wire:navigate :class="$sidebarItemClass">
            <div>{{ __('Treinamentos') }}</div>
        </flux:sidebar.item>

        <flux:sidebar.item icon="calendar" :href="route('app.mentor.ojt.sessions.index')"
            :current="request()->routeIs('app.mentor.ojt.sessions.*')" wire:navigate :class="$sidebarItemClass">
            <div>{{ __('Sessões STP') }}</div>
        </flux:sidebar.item>
    @endcan

</flux:sidebar.group>
