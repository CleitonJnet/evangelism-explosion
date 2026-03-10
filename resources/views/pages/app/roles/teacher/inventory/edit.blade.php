<x-layouts.app :title="__('Editar estoque')">
    <div x-data
        x-init="$nextTick(() => window.Livewire.dispatch('open-teacher-inventory-edit-modal', { inventoryId: {{ $inventory->id }} }))">
        <livewire:pages.app.teacher.inventory.view :inventory="$inventory" />
    </div>
</x-layouts.app>
