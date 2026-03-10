<x-layouts.app :title="__('Sessões STP/OJT')">
    <x-src.toolbar.header :title="__('Sessões STP/OJT')" :description="__('Acompanhe as sessões em que você atua diretamente como mentor.')" />
    <x-src.toolbar.nav>
        <x-src.toolbar.button :href="route('app.mentor.dashboard')" :label="__('Dashboard')" icon="layout-grid" />
        <x-src.toolbar.button :href="route('app.mentor.trainings.index')" :label="__('Treinamentos')" icon="calendar" />
        <x-src.toolbar.button :href="route('app.mentor.ojt.sessions.index')" :label="__('Sessões')" icon="calendar" :active="true" />
    </x-src.toolbar.nav>

    <div class="mt-6">
        <livewire:pages.app.mentor.ojt.sessions />
    </div>
</x-layouts.app>
