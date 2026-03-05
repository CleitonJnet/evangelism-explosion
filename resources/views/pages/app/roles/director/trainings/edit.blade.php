<x-layouts.app :title="__('Editar Treinamento')">
    <x-src.toolbar.header :title="__('Editar treinamento')" :description="__('Atualize os dados do treinamento e mantenha a agenda correta.')" />
    <x-src.toolbar.nav>
        <x-src.toolbar.button :href="route('app.director.training.index')" :label="__('Listar todos')" icon="list"
            :tooltip="__('Lista de treinamentos')" />
        <x-src.toolbar.button :href="route('app.director.training.show', $training)" :label="__('Detalhes')" icon="eye"
            :tooltip="__('Detalhes do treinamento')" />
    </x-src.toolbar.nav>

    <section
        class="rounded-2xl border border-amber-300/20 bg-linear-to-br from-slate-100 via-white to-slate-200 p-6 shadow-lg">
        <livewire:pages.app.director.training.edit :training="$training" />
    </section>
</x-layouts.app>
