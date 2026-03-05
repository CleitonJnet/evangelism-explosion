<x-layouts.app :title="__('Criar Ministério')">
    <x-src.toolbar.header :title="__('Novo ministério')" :description="__('Cadastre um ministério para organizar cursos e treinamentos.')" />
    <x-src.toolbar.nav>
        <x-src.toolbar.button :href="route('app.director.ministry.index')" :label="__('Listar ministérios')" icon="list"
            :tooltip="__('Voltar para listagem de ministérios')" />
    </x-src.toolbar.nav>

    <section
        class="rounded-2xl border border-amber-300/20 bg-linear-to-br from-slate-100 via-white to-slate-200 p-6 shadow-lg">
        <livewire:pages.app.director.ministry.create />
    </section>
</x-layouts.app>
