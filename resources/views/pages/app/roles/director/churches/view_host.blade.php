<x-layouts.app :title="__('Visualizar Base de Treinamentos')">
    <x-src.toolbar.header :title="__('Detalhes da base de treinamento')" :description="__('Consulte os dados da igreja definida como base de treinamentos.')" />
    <x-src.toolbar.nav>
        <x-src.toolbar.button :href="route('app.director.church.index')" :label="__('Listar igrejas')" icon="list"
            :tooltip="__('Voltar para listagem de igrejas')" />
        <x-src.toolbar.button :href="route('app.director.church.edit_host', $church)" :label="__('Editar base')" icon="pencil"
            :tooltip="__('Editar base de treinamentos')" />
    </x-src.toolbar.nav>

    <section
        class="rounded-2xl border border-amber-300/20 bg-linear-to-br from-slate-100 via-white to-slate-200 p-6 shadow-lg">
        <livewire:pages.app.director.church.view-host />
    </section>
</x-layouts.app>
