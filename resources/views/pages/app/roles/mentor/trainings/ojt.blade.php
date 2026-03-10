<x-layouts.app :title="__('STP/OJT do Mentor')">
    <x-src.toolbar.header :title="__('Acompanhamento STP/OJT')" :description="__('Sessões, equipes e abordagens ligadas à sua atuação como mentor neste treinamento.')" />
    <x-src.toolbar.nav>
        <x-src.toolbar.button :href="route('app.mentor.trainings.index')" :label="__('Treinamentos')" icon="list" />
        <x-src.toolbar.button :href="route('app.mentor.trainings.show', $training)" :label="__('Resumo')" icon="book-open-text" />
        <x-src.toolbar.button :href="route('app.mentor.trainings.ojt', $training)" :label="__('STP/OJT')" icon="users-chat" :active="true" />
    </x-src.toolbar.nav>

    <div class="mt-6">
        <livewire:pages.app.mentor.training.ojt :training="$training" />
    </div>
</x-layouts.app>
