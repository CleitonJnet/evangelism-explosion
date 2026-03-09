@php
    $sidebarGroupClass = 'grid [&>div>div]:truncate [&>div>div]:text-slate-300/80';
    $sidebarItemClass =
        'text-slate-100/90 hover:text-white hover:bg-white/10 data-current:text-amber-200 data-current:bg-white/10 data-current:border-amber-200/30 border border-transparent';
@endphp

<flux:sidebar.group :heading="__('Plataforma do Aluno')" :class="$sidebarGroupClass">


    @can('access-student')
        <x-app.menu-sidebar-item label="Treinamentos" :route="route('app.student.training.index')" :current="request()->routeIs('app.student.training.*')" icon="calendar" />
    @endcan

</flux:sidebar.group>
