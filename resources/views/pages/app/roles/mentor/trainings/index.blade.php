<x-layouts.app :title="__('Treinamentos do Mentor')">
    <x-src.toolbar.header :title="__('Treinamentos do mentor')" :description="__('Veja somente os treinamentos em que você está vinculado como mentor.')" />
    <x-src.toolbar.nav>
        <x-src.toolbar.button :href="route('app.mentor.dashboard')" :label="__('Dashboard')" icon="layout-grid" />
        <x-src.toolbar.button :href="route('app.mentor.trainings.index')" :label="__('Treinamentos')" icon="calendar" :active="true" />
        <x-src.toolbar.button :href="route('app.mentor.ojt.sessions.index')" :label="__('Sessões')" icon="calendar" />
    </x-src.toolbar.nav>

    <div class="mt-6">
        <livewire:pages.app.mentor.training.index />
    </div>
</x-layouts.app>
