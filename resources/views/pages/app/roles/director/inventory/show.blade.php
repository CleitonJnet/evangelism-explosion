<x-layouts.app :title="__('Detalhes do estoque')">
    <x-src.toolbar.header :title="__('Detalhes do estoque')" :description="__('Visualize saldos atuais, alertas e histórico auditável do estoque selecionado.')" />
    <x-src.toolbar.nav>
        <x-src.toolbar.button :href="route('app.director.inventory.index')" :label="__('Listar estoques')" icon="list" :tooltip="__('Voltar para listagem')" />
        <x-src.toolbar.button href="#" :label="__('Editar estoque')" icon="pencil" :tooltip="__('Abrir edição em modal na tela atual')"
            class="!border-slate-300 !bg-slate-100 !text-slate-700 hover:!bg-slate-200"
            onclick="window.Livewire.dispatch('open-director-inventory-edit-modal', { inventoryId: {{ $inventory->id }} }); return false;" />
        <span class="mx-1 h-7 w-px bg-slate-300/80"></span>
        <x-src.toolbar.button href="#" :label="__('Novo item simples')" icon="archive-box" :tooltip="__('Cadastrar item simples sem sair desta tela')"
            class="!border-indigo-200 !bg-indigo-50 !text-indigo-800 hover:!bg-indigo-100"
            onclick="window.Livewire.dispatch('open-director-material-create-modal', { type: 'simple' }); return false;" />
        <x-src.toolbar.button href="#" :label="__('Novo composto')" icon="squares-2x2" :tooltip="__('Cadastrar produto composto sem sair desta tela')"
            class="!border-violet-200 !bg-violet-50 !text-violet-800 hover:!bg-violet-100"
            onclick="window.Livewire.dispatch('open-director-material-create-modal', { type: 'composite' }); return false;" />
    </x-src.toolbar.nav>

    <livewire:pages.app.director.inventory.view :inventory="$inventory" />
</x-layouts.app>
