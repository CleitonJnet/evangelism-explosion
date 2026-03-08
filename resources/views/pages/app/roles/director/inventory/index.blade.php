<x-layouts.app :title="__('Estoques')">
    <x-src.toolbar.header :title="__('Gerenciamento de estoques')" :description="__('Acompanhe o estoque central, os estoques dos professores e as movimentações manuais.')" fixed-route-name="app.director.inventory.index" />
    <x-src.toolbar.nav>
        <x-src.toolbar.button :href="route('app.director.inventory.index')" :label="__('Listar estoques')" icon="list" :tooltip="__('Voltar para listagem')" />
        <x-src.toolbar.button :label="__('Novo estoque')" icon="plus" :tooltip="__('Cadastrar novo estoque')"
            onclick="window.Livewire.dispatch('open-director-inventory-create-modal'); return false;" />
    </x-src.toolbar.nav>

    <livewire:pages.app.director.inventory.index />
</x-layouts.app>
