<x-layouts.app :title="__('Editar estoque')">
    <x-src.toolbar.header :title="__('Editar estoque')"
        :description="__('A edição do estoque é aberta automaticamente em modal, mantendo o contexto da operação.')" />
    <x-src.toolbar.nav>
        <x-src.toolbar.button :href="route('app.director.inventory.index')" :label="__('Listar estoques')" icon="list"
            :tooltip="__('Voltar para listagem')" />
        <x-src.toolbar.button :href="route('app.director.inventory.show', $inventory)" :label="__('Detalhes')"
            icon="eye" :tooltip="__('Voltar para os detalhes')" />
    </x-src.toolbar.nav>

    <div x-data
        x-init="$nextTick(() => window.Livewire.dispatch('open-director-inventory-edit-modal', { inventoryId: {{ $inventory->id }} }))">
        <livewire:pages.app.director.inventory.view :inventory="$inventory" />
    </div>
</x-layouts.app>
