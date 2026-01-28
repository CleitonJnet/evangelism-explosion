<x-layouts.app :title="__('My OJT')">
    <x-src.toolbar.bar :title="__('My OJT')" :description="__('Manage your assigned OJT sessions and reports.')">
        <x-src.toolbar.button :href="route('app.mentor.dashboard')" :label="__('Dashboard')" icon="home" />
        <x-src.toolbar.button :href="route('app.mentor.ojt.sessions.index')" :label="__('Sessions')" icon="calendar" />
    </x-src.toolbar.bar>

    <div class="mt-6">
        <livewire:pages.app.mentor.ojt.index />
    </div>
</x-layouts.app>
