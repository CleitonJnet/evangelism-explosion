<x-layouts.app :title="__('Criar Treinamento')">
    <x-src.toolbar.header :title="__('Novo treinamento')" :description="__('Cadastre as informações do treinamento e organize a agenda do evento.')" />
    <x-src.toolbar.nav>
        <x-src.toolbar.button :href="route('app.director.training.index')" :label="__('Listar todos')" icon="list" :tooltip="__('Lista de treinamentos')" />
    </x-src.toolbar.nav>

    <livewire:pages.app.director.training.create />
</x-layouts.app>
