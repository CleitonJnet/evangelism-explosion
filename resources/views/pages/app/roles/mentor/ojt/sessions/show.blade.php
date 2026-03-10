<x-layouts.app :title="__('Sessão STP/OJT')">
    <x-src.toolbar.header :title="__('Sessão STP/OJT')" :description="__('Detalhes somente leitura da sessão e das equipes vinculadas à sua mentoria.')" />
    <x-src.toolbar.nav>
        <x-src.toolbar.button :href="route('app.mentor.ojt.sessions.index')" :label="__('Sessões')" icon="arrow-left" />
        <x-src.toolbar.button :href="route('app.mentor.trainings.show', $session->training)" :label="__('Treinamento')" icon="book-open-text" />
        <x-src.toolbar.button :href="route('app.mentor.trainings.ojt', $session->training)" :label="__('STP/OJT')" icon="users-chat" />
    </x-src.toolbar.nav>

    <div class="mt-6">
        <livewire:pages.app.mentor.ojt.session-show :session="$session" />
    </div>
</x-layouts.app>
