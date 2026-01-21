<x-layouts.app :title="__('Cadastrar Produto')">
    <ul>
        <li><a href="{{ route('app.director.inventory.index') }}">Lista de Produtos</a></li>
    </ul>
    <hr>
    <livewire:pages.app.director.inventory.create />
</x-layouts.app>
