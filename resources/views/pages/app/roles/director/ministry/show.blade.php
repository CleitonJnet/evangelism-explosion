<x-layouts.app :title="__('Visualizar Ministérios')">
    <x-src.toolbar.header :title="__('Detalhes do ministério')" :description="__('Consulte dados do ministério e gerencie os cursos vinculados.')" />
    <x-src.toolbar.nav>
        <x-src.toolbar.button :href="route('app.director.ministry.index')" :label="__('Listar ministérios')" icon="list"
            :tooltip="__('Voltar para listagem de ministérios')" />
        <x-src.toolbar.button :label="__('Editar')" icon="pencil"
            onclick="window.Livewire.dispatch('open-director-ministry-edit-modal'); return false;"
            :tooltip="__('Editar ministério')" />
        <x-src.toolbar.button :href="route('app.director.ministry.course.create', $ministry)" :label="__('Novo curso')"
            icon="plus" :tooltip="__('Cadastrar curso neste ministério')" />
    </x-src.toolbar.nav>

    <section
        class="rounded-2xl border border-amber-300/20 bg-linear-to-br from-slate-100 via-white to-slate-200 p-6 shadow-lg">
        <livewire:pages.app.director.ministry.view :ministry="$ministry" />
    </section>

    <livewire:pages.app.director.ministry.edit-modal :ministry-id="$ministry->id"
        wire:key="director-ministry-edit-modal-{{ $ministry->id }}" />
</x-layouts.app>
