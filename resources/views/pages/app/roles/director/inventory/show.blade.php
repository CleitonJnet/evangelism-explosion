<x-layouts.app :title="__('Detalhes do estoque')">
    <x-src.toolbar.header :title="__('Detalhes do estoque')"
        :description="__('Visualize saldos atuais, alertas e histórico auditável do estoque selecionado.')" />
    <x-src.toolbar.nav>
        <x-src.toolbar.button :href="route('app.director.inventory.index')" :label="__('Listar estoques')" icon="list"
            :tooltip="__('Voltar para listagem')" />
        <x-src.toolbar.button href="#" :label="__('Editar estoque')" icon="pencil"
            :tooltip="__('Abrir edição em modal na tela atual')"
            onclick="window.Livewire.dispatch('open-director-inventory-edit-modal', { inventoryId: {{ $inventory->id }} }); return false;" />
        <span class="mx-1 h-7 w-px bg-slate-300/80"></span>
        <x-src.toolbar.button href="#" :label="__('Entrada manual')" icon="plus"
            :tooltip="__('Registrar entrada manual neste estoque')"
            onclick="window.Livewire.dispatch('open-director-inventory-stock-action-modal', { inventoryId: {{ $inventory->id }}, mode: 'entry' }); return false;" />
        <x-src.toolbar.button href="#" :label="__('Saída manual')" icon="arrow-left"
            :tooltip="__('Registrar saída manual neste estoque')"
            onclick="window.Livewire.dispatch('open-director-inventory-stock-action-modal', { inventoryId: {{ $inventory->id }}, mode: 'exit' }); return false;" />
        <x-src.toolbar.button href="#" :label="__('Ajuste')" icon="check"
            :tooltip="__('Ajustar saldo consolidado deste estoque')"
            onclick="window.Livewire.dispatch('open-director-inventory-stock-action-modal', { inventoryId: {{ $inventory->id }}, mode: 'adjustment' }); return false;" />
        <x-src.toolbar.button href="#" :label="__('Registrar perda')" icon="x"
            :tooltip="__('Registrar perda ou avaria neste estoque')"
            onclick="window.Livewire.dispatch('open-director-inventory-stock-action-modal', { inventoryId: {{ $inventory->id }}, mode: 'loss' }); return false;" />
        <x-src.toolbar.button href="#" :label="__('Transferir')" icon="calendar-check"
            :tooltip="__('Transferir saldo para outro estoque')"
            onclick="window.Livewire.dispatch('open-director-inventory-transfer-modal', { inventoryId: {{ $inventory->id }} }); return false;" />
    </x-src.toolbar.nav>

    <livewire:pages.app.director.inventory.view :inventory="$inventory" />
</x-layouts.app>
