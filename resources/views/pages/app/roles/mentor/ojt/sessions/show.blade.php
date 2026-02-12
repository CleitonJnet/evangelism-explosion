<x-layouts.app :title="__('OJT Session')">
    <x-src.toolbar.bar :title="__('OJT Session')" :description="__('Session details and your teams.')">
        <x-src.toolbar.button :href="route('app.mentor.ojt.sessions.index')" :label="__('Back to sessions')" icon="arrow-left" />
    </x-src.toolbar.bar>

    <div class="mt-6">
        <livewire:pages.app.mentor.ojt.session-show :session="$session" />
    </div>
</x-layouts.app>
