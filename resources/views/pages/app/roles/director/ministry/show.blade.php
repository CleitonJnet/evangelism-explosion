<x-layouts.app :title="__('Visualizar Ministérios')">
    <x-src.toolbar.header :title="__('Detalhes do ministério')" :description="__('Consulte dados do ministério e gerencie os cursos vinculados.')" />
    <x-src.toolbar.nav>
        <x-src.toolbar.button :href="route('app.director.ministry.index')" :label="__('Listar ministérios')" icon="list" :tooltip="__('Voltar para listagem de ministérios')" />
        <x-src.toolbar.button :label="__('Editar')" icon="pencil"
            onclick="window.Livewire.dispatch('open-director-ministry-edit-modal'); return false;" :tooltip="__('Editar ministério')" />
        <x-src.toolbar.button :label="__('Novo curso')" icon="plus"
            onclick="window.Livewire.dispatch('open-director-course-create-modal'); return false;" :tooltip="__('Cadastrar curso neste ministério')" />
    </x-src.toolbar.nav>

    <livewire:pages.app.director.ministry.view :ministry="$ministry" />

    <livewire:pages.app.director.ministry.edit-modal :ministry-id="$ministry->id"
        wire:key="director-ministry-edit-modal-{{ $ministry->id }}" />
    <livewire:pages.app.director.course.create-modal :ministry="$ministry"
        wire:key="director-course-create-modal-{{ $ministry->id }}" />
</x-layouts.app>
