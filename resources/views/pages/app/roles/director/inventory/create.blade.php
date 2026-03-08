<x-layouts.app :title="__('Novo estoque')">
    <x-src.toolbar.header :title="__('Novo estoque')"
        :description="__('O cadastro é aberto automaticamente em modal para manter o fluxo operacional enxuto.')" fixed-route-name="app.director.inventory.create" />
    <x-src.toolbar.nav>
        <x-src.toolbar.button :href="route('app.director.inventory.index')" :label="__('Listar estoques')" icon="list"
            :tooltip="__('Voltar para listagem')" />
    </x-src.toolbar.nav>

    <div x-data x-init="$nextTick(() => window.Livewire.dispatch('open-director-inventory-create-modal'))">
        <livewire:pages.app.director.inventory.index />
    </div>
</x-layouts.app>
