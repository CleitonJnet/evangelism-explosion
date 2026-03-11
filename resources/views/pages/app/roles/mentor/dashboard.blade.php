<x-layouts.app :title="__('Dashboard do Mentor')">
    <x-src.toolbar.header :title="__('Painel do mentor')" :description="__('Acompanhe seus treinamentos, sessões STP e equipes vinculadas sem expor dados sensíveis do evento.')" />
    <x-src.toolbar.nav>
        <x-src.toolbar.button :href="route('app.mentor.dashboard')" :label="__('Dashboard')" icon="layout-grid" :active="request()->routeIs('app.mentor.dashboard')" />
        <x-src.toolbar.button :href="route('app.mentor.trainings.index')" :label="__('Treinamentos')" icon="calendar" :active="request()->routeIs('app.mentor.trainings.*')" />
        <x-src.toolbar.button :href="route('app.mentor.ojt.sessions.index')" :label="__('Sessões')" icon="calendar" :active="request()->routeIs('app.mentor.ojt.sessions.*')" />
    </x-src.toolbar.nav>

    <div class="mt-6">
        <livewire:pages.app.mentor.dashboard />
    </div>
</x-layouts.app>
