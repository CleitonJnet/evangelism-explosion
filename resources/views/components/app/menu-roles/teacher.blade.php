<flux:sidebar.group :heading="__('Plataforma do Professor')" class="[&>div>div]:truncate">


    @can('access-teacher')
        <x-app.menu-sidebar-item label="Dashboard" :route="route('app.teacher.dashboard')" :current="request()->routeIs('app.teacher.dashboard')" icon="layout-grid" />
        <x-app.menu-sidebar-item label="Igrejas" :route="route('app.teacher.churches.index')" :current="request()->routeIs('app.teacher.churches.*')" icon="calendar" />
        <x-app.menu-sidebar-item label="Treinamentos" :route="route('app.teacher.trainings.index')" :current="request()->routeIs('app.teacher.trainings.*')" icon="calendar" />
        <x-app.menu-sidebar-item label="Estoque" :route="route('app.teacher.inventory.index')" :current="request()->routeIs('app.teacher.inventory.*')" icon="archive-box" />
    @endcan

</flux:sidebar.group>
