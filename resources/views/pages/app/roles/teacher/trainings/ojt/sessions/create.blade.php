<x-layouts.app :title="__('Create OJT Session')">
    <x-src.toolbar.bar :title="__('On-The-Job Training')" :description="__('Create a new OJT session for this training.')">
        <x-src.toolbar.button :href="route('app.teacher.trainings.ojt.sessions.index', $training)" :label="__('Back to sessions')" icon="arrow-left" />
    </x-src.toolbar.bar>

    <div class="mt-6 rounded-2xl border border-[color:var(--ee-app-border)] bg-white p-6">
        <flux:text class="text-sm text-[color:var(--ee-app-muted)]">
            {{ __('Session create form will be implemented here.') }}
        </flux:text>
    </div>
</x-layouts.app>
