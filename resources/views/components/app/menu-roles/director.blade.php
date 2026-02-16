@php
    $sidebarGroupClass = 'grid [&>div>div]:text-slate-300/80';
    $sidebarItemClass =
        'text-slate-100/95 hover:text-amber-100 hover:bg-white/15 data-current:text-amber-200 data-current:bg-white/12 data-current:border-amber-200/30 border border-transparent';
@endphp

<flux:sidebar.group :heading="__('Plataforma')" :class="$sidebarGroupClass">


    @can('access-director')
        <x-app.menu-sidebar-item label="Dashboard" :route="route('app.director.dashboard')" :current="request()->routeIs('app.director.dashboard')" icon="layout-grid" />
        <x-app.menu-sidebar-item label="Igrejas" :route="route('app.director.church.index')" :current="request()->routeIs('app.director.church.*')" icon="home" />
        <x-app.menu-sidebar-item label="Ministérios" :route="route('app.director.ministry.index')" :current="request()->routeIs('app.director.ministry.*')" icon="home" />
        <x-app.menu-sidebar-item label="Treinamentos" :route="route('app.director.training.index')" :current="request()->routeIs('app.director.training.*')" icon="home" />
        <x-app.menu-sidebar-item label="Estoque" :route="route('app.director.inventory.index')" :current="request()->routeIs('app.director.inventory.*')" icon="home" />
        <x-app.menu-sidebar-item label="Website" :route="route('app.director.testimonials')" :current="request()->routeIs('app.director.testimonials')" icon="home" />
    @endcan

</flux:sidebar.group>
