<x-layouts.app :title="__('Editar Ministério')">
    <x-src.toolbar.header :title="__('Editar ministério')" :description="__('Atualize os dados do ministério selecionado.')" />
    <x-src.toolbar.nav>
        <x-src.toolbar.button :href="route('app.director.ministry.index')" :label="__('Listar ministérios')" icon="list"
            :tooltip="__('Voltar para listagem de ministérios')" />
        <x-src.toolbar.button :href="route('app.director.ministry.show', $ministry)" :label="__('Detalhes')" icon="eye"
            :tooltip="__('Detalhes do ministério')" />
    </x-src.toolbar.nav>

    <section
        class="rounded-2xl border border-amber-300/20 bg-linear-to-br from-slate-100 via-white to-slate-200 p-6 shadow-lg">
        <livewire:pages.app.director.ministry.edit :ministry="$ministry->id" />
    </section>
</x-layouts.app>
