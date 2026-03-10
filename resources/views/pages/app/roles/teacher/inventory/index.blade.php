<x-layouts.app :title="__('Meu estoque')">
    <x-src.toolbar.header :title="__('Gerenciamento do meu estoque')" :description="__('Acompanhe apenas os estoques delegados a você e entre rapidamente na rotina operacional de cada local sob sua responsabilidade.')" fixed-route-name="app.teacher.inventory.index" />

    <x-src.toolbar.nav>
        <x-src.toolbar.button :href="route('app.teacher.inventory.index')" :label="__('Meus estoques')" icon="list" :tooltip="__('Atualizar a listagem dos estoques sob sua responsabilidade')" />
    </x-src.toolbar.nav>

    <livewire:pages.app.teacher.inventory.index />
</x-layouts.app>
