<x-layouts.app :title="__('OJT Report')">
    <x-src.toolbar.bar :title="__('OJT Report')" :description="__('Submit your OJT report.')">
        <x-src.toolbar.button :href="route('app.mentor.ojt.sessions.index')" :label="__('Back to sessions')" icon="arrow-left" />
    </x-src.toolbar.bar>

    <div class="mt-6">
        <livewire:pages.app.mentor.ojt.report-form :team="$team" />
    </div>
</x-layouts.app>
