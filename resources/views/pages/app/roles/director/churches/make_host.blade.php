<x-layouts.app :title="__('Cadastrar Base de Treinamentos')">
    <x-src.toolbar.header :title="__('Nova base de treinamento')" :description="__('Defina uma igreja como base para treinamentos e eventos.')" />
    <x-src.toolbar.nav>
        <x-src.toolbar.button :href="route('app.director.church.index')" :label="__('Listar igrejas')" icon="list"
            :tooltip="__('Voltar para listagem de igrejas')" />
    </x-src.toolbar.nav>

    <section
        class="rounded-2xl border border-amber-300/20 bg-linear-to-br from-slate-100 via-white to-slate-200 p-6 shadow-lg">
        <livewire:pages.app.director.church.make-host />
    </section>
</x-layouts.app>
