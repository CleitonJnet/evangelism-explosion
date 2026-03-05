<x-layouts.app :title="__('Listar Ministérios')">
    <x-src.toolbar.header :title="__('Gerenciamento de ministérios')" :description="__('Organize ministérios e seus cursos para uso nos treinamentos e eventos.')" />
    <x-src.toolbar.nav>
        <x-src.toolbar.button :label="__('Novo ministério')" icon="plus"
            onclick="window.Livewire.dispatch('open-director-ministry-create-modal'); return false;"
            :tooltip="__('Cadastrar novo ministério')" />
    </x-src.toolbar.nav>

    <livewire:pages.app.director.ministry.index />
    <livewire:pages.app.director.ministry.create-modal wire:key="director-ministry-create-modal" />
</x-layouts.app>
