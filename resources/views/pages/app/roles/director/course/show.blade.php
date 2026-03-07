<x-layouts.app :title="__('Visualizar Curso')">
    <x-src.toolbar.header :title="__('Detalhes do curso')" :description="__('Consulte os dados do curso e mantenha o conteúdo atualizado.')" />
    <x-src.toolbar.nav>
        <x-src.toolbar.button :href="route('app.director.ministry.show', $ministry)" :label="__('Voltar ao ministério')" icon="list" :tooltip="__('Detalhes do ministério')" />
        <x-src.toolbar.button :href="route('app.director.ministry.course.sections', ['ministry' => $ministry, 'course' => $course])" :label="__('Unidades')" icon="calendar" :tooltip="__('Gerenciar unidades do curso')" />
        <x-src.toolbar.button :label="__('Editar curso')" icon="pencil"
            onclick="window.Livewire.dispatch('open-director-course-edit-modal'); return false;" :tooltip="__('Editar curso')" />
    </x-src.toolbar.nav>

    <livewire:pages.app.director.course.view :course="$course" />
    <livewire:pages.app.director.course.edit-modal :course-id="$course->id"
        wire:key="director-course-edit-modal-{{ $course->id }}" />
</x-layouts.app>
