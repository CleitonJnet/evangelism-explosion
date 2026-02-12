<x-layouts.app :title="__('Editar Produto')">
    <ul>
        <li><a href="{{ route('app.director.inventory.show', $inventory) }}">Visualizar Produto</a></li>
    </ul>
    <hr>
    <livewire:pages.app.director.inventory.edit :inventory="$inventory" />
</x-layouts.app>
