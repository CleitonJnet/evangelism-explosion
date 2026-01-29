<x-layouts.app :title="__('Editar Treinamento')">
    <x-src.toolbar.bar :title="__('Editar treinamento')" :description="__('Atualize os dados do treinamento e mantenha a agenda correta.')">
        <x-src.toolbar.button :href="route('app.teacher.training.show', $training)" :label="__('Detalhes')" icon="eye" :tooltip="__('Detalhes do treinamento')" />
    </x-src.toolbar.bar>

    <livewire:pages.app.teacher.training.edit :training="$training" />
</x-layouts.app>
