<x-layouts.app :title="__('Listar Produtos')">

    <ul class="flex gap-6 flex-wrap">
        <li><a href="{{ route('app.director.inventory.create') }}">Cadastrar Produto</a></li>
    </ul>
    <hr>
    <livewire:pages.app.director.inventory.index />

</x-layouts.app>
