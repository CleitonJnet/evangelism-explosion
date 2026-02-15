<x-layouts.app :title="__('Listar Treinamentos')">
    <x-src.toolbar.header :title="__('Gerenciamento de Treinamentos e Eventos')" :description="__('Controle os treinamentos do Evangelismo Explosivo, organizando status e cursos em um só lugar.')" />
    <x-src.toolbar.nav>
        <div id="app-toolbar" class="w-full"></div>
        @stack('app-toolbar')
    </x-src.toolbar.nav>

    <livewire:pages.app.teacher.training.index :status-key="$statusKey ?? 'scheduled'" />
</x-layouts.app>
