<x-layouts.app :title="__('Editar Base de Treinamento')">
    <x-src.toolbar.header :title="__('Editar base de treinamento')" :description="__('Ajuste os dados da base de treinamentos selecionada.')" />
    <x-src.toolbar.nav>
        <x-src.toolbar.button :href="route('app.director.church.index')" :label="__('Listar igrejas')" icon="list"
            :tooltip="__('Voltar para listagem de igrejas')" />
        <x-src.toolbar.button :href="route('app.director.church.view_host', $church)" :label="__('Detalhes')" icon="eye"
            :tooltip="__('Detalhes da base')" />
    </x-src.toolbar.nav>

    {{-- <livewire:pages.app.director.church.edit-host :church="$church" /> --}}
</x-layouts.app>
