<x-layouts.app :title="__('Visualizar Curso')">
    <x-src.toolbar.header :title="__('Detalhes do curso')" :description="__('Consulte os dados do curso e mantenha o conteúdo atualizado.')" />
    <x-src.toolbar.nav>
        <x-src.toolbar.button :href="route('app.director.ministry.show', $ministry)" :label="__('Voltar ao ministério')" icon="list"
            :tooltip="__('Detalhes do ministério')" />
        <x-src.toolbar.button :href="route('app.director.ministry.course.edit', ['ministry' => $ministry, 'course' => $course])"
            :label="__('Editar curso')" icon="pencil" :tooltip="__('Editar curso')" />
    </x-src.toolbar.nav>

    <section
        class="rounded-2xl border border-amber-300/20 bg-linear-to-br from-slate-100 via-white to-slate-200 p-6 shadow-lg">
        <livewire:pages.app.director.course.view :course="$course" />
    </section>
</x-layouts.app>
