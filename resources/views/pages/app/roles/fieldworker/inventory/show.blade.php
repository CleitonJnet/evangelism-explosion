<x-layouts.app :title="__('Visualizar Produto')">
    <ul class="flex gap-6 flex-wrap">
        <li><a href="{{ route('app.director.inventory.index') }}">Listar Produtos</a></li>
        <li><a href="{{ route('app.director.inventory.edit', $inventory) }}">Editar Produto</a></li>
    </ul>
    <hr>
    <livewire:pages.app.director.inventory.view :inventory="$inventory" />
</x-layouts.app>
