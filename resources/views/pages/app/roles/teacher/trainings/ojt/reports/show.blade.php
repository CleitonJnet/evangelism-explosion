<x-layouts.app :title="__('OJT Report')">
    <x-src.toolbar.bar :title="__('On-The-Job Training')" :description="__('View the submitted OJT report.')">
        <x-src.toolbar.button :href="route('app.teacher.trainings.ojt.reports.index', $training)" :label="__('Back to reports')" icon="arrow-left" />
    </x-src.toolbar.bar>

    <div class="mt-6 rounded-2xl border border-[color:var(--ee-app-border)] bg-white p-6">
        <flux:text class="text-sm text-[color:var(--ee-app-muted)]">
            {{ __('Report detail view will be implemented here.') }}
        </flux:text>
    </div>
</x-layouts.app>
