<x-layouts.app :title="__('Editar Curso')">
    <x-src.toolbar.header :title="__('Editar curso')" :description="__('Atualize os dados do curso vinculado ao ministério.')" />
    <x-src.toolbar.nav>
        <x-src.toolbar.button :href="route('app.director.ministry.show', $ministry)" :label="__('Voltar ao ministério')" icon="list"
            :tooltip="__('Detalhes do ministério')" />
        <x-src.toolbar.button :href="route('app.director.ministry.course.show', ['ministry' => $ministry, 'course' => $course])"
            :label="__('Detalhes do curso')" icon="eye" :tooltip="__('Detalhes do curso')" />
    </x-src.toolbar.nav>

    <section
        class="rounded-2xl border border-amber-300/20 bg-linear-to-br from-slate-100 via-white to-slate-200 p-6 shadow-lg">
        <livewire:pages.app.director.course.edit :course="$course" />
    </section>
</x-layouts.app>
