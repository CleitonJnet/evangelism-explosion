<x-layouts.app :title="__('Listar Treinamentos')">
    <x-src.toolbar.bar :title="__('Gerenciamento de Treinamentos e Eventos')" :description="__('Controle os treinamentos do Evangelismo Explosivo, organizando status e cursos em um só lugar.')">
        <div id="app-toolbar" class="w-full"></div>
        @stack('app-toolbar')
    </x-src.toolbar.bar>

    <livewire:pages.app.director.training.index :status-key="$statusKey ?? 'scheduled'" />
</x-layouts.app>
