<x-layouts.app :title="__('Listar Treinamentos')">
    <livewire:pages.app.teacher.training.index :status-key="$statusKey ?? 'scheduled'" />
</x-layouts.app>
