<x-layouts.app :title="__('Detalhes do estoque')">
    <x-src.toolbar.header :title="__('Detalhes do estoque')" :description="__('Visualize saldos atuais, alertas e histórico auditável do estoque selecionado.')" />

    <x-src.toolbar.nav x-data="{ hasActiveSimpleMaterials: @js($hasActiveSimpleMaterials) }"
        x-on:director-material-created.window="
            if (($event.detail?.type ?? null) === 'simple') {
                hasActiveSimpleMaterials = ($event.detail?.hasActiveSimpleMaterials ?? false)
            }
        "
        x-on:director-material-updated.window="
            if (($event.detail?.type ?? null) === 'simple') {
                hasActiveSimpleMaterials = ($event.detail?.hasActiveSimpleMaterials ?? false)
            }
        "
        x-on:director-material-deleted.window="
            if (($event.detail?.type ?? null) === 'simple') {
                hasActiveSimpleMaterials = ($event.detail?.hasActiveSimpleMaterials ?? false)
            }
        ">
        <x-src.toolbar.button :href="route('app.director.inventory.index')" :label="__('Listar estoques')" icon="list" :tooltip="__('Voltar para listagem')" />
        <x-src.toolbar.button href="#" :label="__('Editar estoque')" icon="pencil" :tooltip="__('Abrir edição em modal na tela atual')"
            class="!border-slate-300 !bg-slate-100 !text-slate-700 hover:!bg-slate-200"
            onclick="window.Livewire.dispatch('open-director-inventory-edit-modal', { inventoryId: {{ $inventory->id }} }); return false;" />
        <span class="mx-1 h-7 w-px bg-slate-300/80"></span>
        <x-src.toolbar.button href="#" :label="__('Novo item simples')" icon="archive-box" :tooltip="__('Cadastrar item simples sem sair desta tela')"
            class="!border-indigo-200 !bg-indigo-50 !text-indigo-800 hover:!bg-indigo-100"
            onclick="window.Livewire.dispatch('open-director-material-create-modal', { type: 'simple' }); return false;" />

        <div x-show="hasActiveSimpleMaterials" x-cloak>
            <x-src.toolbar.button href="#" :label="__('Novo composto')" icon="squares-2x2" :tooltip="__('Cadastrar produto composto sem sair desta tela')"
                class="!border-violet-200 !bg-violet-50 !text-violet-800 hover:!bg-violet-100"
                onclick="window.Livewire.dispatch('open-director-material-create-modal', { type: 'composite' }); return false;" />
        </div>

        <div x-show="!hasActiveSimpleMaterials" x-cloak>
            <x-src.toolbar.button href="#" :label="__('Novo composto')" icon="squares-2x2" :tooltip="__('Cadastre ou mantenha pelo menos um item simples ativo para liberar produtos compostos')"
                class="pointer-events-none !border-slate-300 !bg-slate-100 !text-slate-400 hover:!bg-slate-100 opacity-70" />
        </div>
    </x-src.toolbar.nav>

    <livewire:pages.app.director.inventory.view :inventory="$inventory" />
</x-layouts.app>
