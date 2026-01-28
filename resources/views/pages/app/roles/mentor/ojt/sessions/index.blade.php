<x-layouts.app :title="__('My OJT Sessions')">
    <x-src.toolbar.bar :title="__('My OJT Sessions')" :description="__('Review your assigned OJT sessions.')">
        <x-src.toolbar.button :href="route('app.mentor.dashboard')" :label="__('Dashboard')" icon="home" />
        <x-src.toolbar.button :href="route('app.mentor.ojt.sessions.index')" :label="__('Sessions')" icon="calendar" :active="true" />
    </x-src.toolbar.bar>

    <div class="mt-6">
        <livewire:pages.app.mentor.ojt.sessions />
    </div>
</x-layouts.app>
