<x-layouts.app :title="__('OJT Reports')">
    <x-src.toolbar.bar :title="__('On-The-Job Training')" :description="__('Review mentor OJT reports for this training.')">
        <x-src.toolbar.button :href="route('app.teacher.trainings.index')" :label="__('List trainings')" icon="list" :tooltip="__('All trainings')" />
        <x-src.toolbar.button :href="route('app.teacher.trainings.show', $training)" :label="__('Details')" icon="eye" :tooltip="__('Training details')" />
        <x-src.toolbar.button :href="route('app.teacher.trainings.schedule', $training)" :label="__('Schedule')" icon="calendar" :tooltip="__('Training schedule')" />
        <x-src.toolbar.button :href="route('app.teacher.trainings.ojt.sessions.index', $training)" :label="__('OJT')" icon="users-chat" :active="true"
            :tooltip="__('On-The-Job Training')" />
    </x-src.toolbar.bar>

    <div class="mt-4 flex flex-wrap items-center justify-center gap-2 text-sm">
        <x-src.toolbar.button :href="route('app.teacher.trainings.ojt.sessions.index', $training)" :label="__('Sessions')" icon="calendar-check" />
        <x-src.toolbar.button :href="route('app.teacher.trainings.ojt.teams.index', $training)" :label="__('Teams')" icon="users" />
        <x-src.toolbar.button :href="route('app.teacher.trainings.ojt.reports.index', $training)" :label="__('Reports')" icon="document-text" :active="true" />
        <x-src.toolbar.button :href="route('app.teacher.trainings.ojt.stats.summary', $training)" :label="__('Statistics')" icon="chart-bar" />
    </div>

    <div class="mt-6">
        <livewire:pages.app.teacher.training.ojt.reports :training="$training" />
    </div>
</x-layouts.app>
