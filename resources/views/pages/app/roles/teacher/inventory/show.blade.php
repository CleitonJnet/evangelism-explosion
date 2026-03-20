<x-layouts.app :title="__('Detalhes do estoque')">
    <x-src.toolbar.header :title="__('Detalhes do meu estoque')" :description="__('Visualize saldos atuais, alertas e histórico auditável do estoque delegado ao seu perfil. Para movimentar um produto, clique diretamente na linha desejada na tabela abaixo.')" />

    <x-src.toolbar.nav>
        <x-src.toolbar.button :href="route('app.teacher.inventory.index')" :label="__('Listar meus estoques')" icon="list" :tooltip="__('Voltar para a listagem')" />
        <x-src.toolbar.button href="#" :label="__('Adicionar itens')" icon="plus" :tooltip="__('Selecionar itens simples já cadastrados para vincular ao seu estoque')"
            class="!border-emerald-200 !bg-emerald-50 !text-emerald-700 hover:!bg-emerald-100"
            onclick="window.Livewire.dispatch('open-teacher-inventory-attach-simple-materials-modal', { inventoryId: {{ $inventory->id }} }); return false;" />
        <x-src.toolbar.button href="#" :label="__('Editar estoque')" icon="pencil" :tooltip="__('Abrir edição em modal na tela atual')"
            class="!border-slate-300 !bg-slate-100 !text-slate-700 hover:!bg-slate-200"
            onclick="window.Livewire.dispatch('open-teacher-inventory-edit-modal', { inventoryId: {{ $inventory->id }} }); return false;" />
        <div class="ml-auto">
            <x-src.toolbar.button href="#" :label="__('Excluir estoque')" icon="trash"
                :tooltip="__('Abrir confirmação para excluir este estoque')"
                class="!border-rose-200 !bg-rose-50 !text-rose-700 hover:!bg-rose-100"
                onclick="window.Livewire.dispatch('open-teacher-inventory-delete-modal'); return false;" />
        </div>
    </x-src.toolbar.nav>

    <livewire:pages.app.teacher.inventory.view :inventory="$inventory" />
</x-layouts.app>
