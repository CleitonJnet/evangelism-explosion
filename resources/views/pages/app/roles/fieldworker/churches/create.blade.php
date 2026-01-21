<x-layouts.app :title="__('Criar Igreja')">
    <ul>
        <li><a href="{{ route('app.director.church.index') }}">Lista de Igrejas</a></li>
    </ul>
    <hr>
    <livewire:pages.app.director.church.create />
</x-layouts.app>
