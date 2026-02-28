<flux:sidebar.group :heading="__('Platform')">


    @can('access-teacher')
        <x-app.menu-sidebar-item label="Dashboard" :route="route('app.teacher.dashboard')" :current="request()->routeIs('app.teacher.dashboard')" icon="layout-grid" />
        <x-app.menu-sidebar-item label="Treinamentos" :route="route('app.teacher.trainings.index')" :current="request()->routeIs('app.teacher.trainings.*')" icon="calendar" />
        <x-app.menu-sidebar-item label="Igrejas" :route="route('app.teacher.churches.index')" :current="request()->routeIs('app.teacher.churches.*')" icon="calendar" />
    @endcan

</flux:sidebar.group>
