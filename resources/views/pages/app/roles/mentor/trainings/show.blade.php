<x-layouts.app :title="__('Treinamento do Mentor')">
    <x-src.toolbar.header :title="__('Visão do treinamento')" :description="__('Dados básicos do evento e resumo seguro da prática STP vinculada à sua mentoria.')" />
    <x-src.toolbar.nav>
        <x-src.toolbar.button :href="route('app.mentor.trainings.index')" :label="__('Treinamentos')" icon="list" />
        <x-src.toolbar.button :href="route('app.mentor.trainings.show', $training)" :label="__('Resumo')" icon="book-open-text" :active="request()->routeIs('app.mentor.trainings.show')" />
        <x-src.toolbar.button :href="route('app.mentor.trainings.ojt', $training)" :label="__('STP')" icon="users-chat" :active="request()->routeIs('app.mentor.trainings.ojt')" />
    </x-src.toolbar.nav>

    <div class="mt-6">
        <livewire:pages.app.mentor.training.view :training="$training" />
    </div>
</x-layouts.app>
