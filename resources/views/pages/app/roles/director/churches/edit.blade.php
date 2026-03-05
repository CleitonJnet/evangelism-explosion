<x-layouts.app :title="__('Editar Igreja')">
    <x-src.toolbar.header :title="__('Editar igreja')" :description="__('Atualize os dados cadastrais da igreja selecionada.')" />
    <x-src.toolbar.nav>
        <x-src.toolbar.button :href="route('app.director.church.index')" :label="__('Listar igrejas')" icon="list"
            :tooltip="__('Voltar para listagem de igrejas')" />
        <x-src.toolbar.button :href="route('app.director.church.show', $church)" :label="__('Detalhes')" icon="eye"
            :tooltip="__('Detalhes da igreja')" />
    </x-src.toolbar.nav>

    <section
        class="rounded-2xl border border-amber-300/20 bg-linear-to-br from-slate-100 via-white to-slate-200 p-6 shadow-lg">
        <livewire:pages.app.director.church.edit :church="$church" />
    </section>
</x-layouts.app>
