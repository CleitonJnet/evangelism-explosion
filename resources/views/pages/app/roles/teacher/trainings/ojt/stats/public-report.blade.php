<x-layouts.app :title="__('OJT Public Report')">
    <x-src.toolbar.bar :title="__('On-The-Job Training')" :description="__('Public report highlights from OJT.')">
        <x-src.toolbar.button :href="route('app.teacher.training.index')" :label="__('List trainings')" icon="list" :tooltip="__('All trainings')" />
        <x-src.toolbar.button :href="route('app.teacher.training.show', $training)" :label="__('Details')" icon="calendar" :tooltip="__('Training details')" />
        <x-src.toolbar.button :href="route('app.teacher.training.schedule', $training)" :label="__('Schedule')" icon="calendar" :tooltip="__('Training schedule')" />
        <x-src.toolbar.button :href="route('app.teacher.trainings.ojt.sessions.index', $training)" :label="__('OJT')" icon="users" :active="true"
            :tooltip="__('On-The-Job Training')" />
        <x-src.toolbar.button :href="route('app.teacher.training.edit', $training)" :label="__('Edit')" icon="pencil" :tooltip="__('Edit training')" />
    </x-src.toolbar.bar>

    <div class="mt-4 flex flex-wrap items-center gap-2 text-sm">
        <x-src.toolbar.button :href="route('app.teacher.trainings.ojt.sessions.index', $training)" :label="__('Sessions')" icon="calendar" />
        <x-src.toolbar.button :href="route('app.teacher.trainings.ojt.teams.index', $training)" :label="__('Teams')" icon="users" />
        <x-src.toolbar.button :href="route('app.teacher.trainings.ojt.reports.index', $training)" :label="__('Reports')" icon="document-text" />
        <x-src.toolbar.button :href="route('app.teacher.trainings.ojt.stats.summary', $training)" :label="__('Statistics')" icon="chart-bar" :active="true" />
    </div>

    <div class="mt-6">
        <livewire:pages.app.teacher.training.ojt.statistics :training="$training" mode="public" />
    </div>
</x-layouts.app>
