<x-layouts.app :title="__('Criar Curso')">
    <x-src.toolbar.header :title="__('Novo curso')" :description="__('Cadastre um curso para uso em treinamentos e eventos do ministério.')" />
    <x-src.toolbar.nav>
        <x-src.toolbar.button :href="route('app.director.ministry.show', $ministry)" :label="__('Voltar ao ministério')" icon="list"
            :tooltip="__('Detalhes do ministério')" />
    </x-src.toolbar.nav>

    <section
        class="rounded-2xl border border-amber-300/20 bg-linear-to-br from-slate-100 via-white to-slate-200 p-6 shadow-lg">
        <livewire:pages.app.director.course.create :ministry="$ministry" />
    </section>
</x-layouts.app>
