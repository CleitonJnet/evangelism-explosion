<x-layouts.app :title="__('Criar Igreja')">
    <ul>
        <li><a href="{{ route('app.director.church.show', $church) }}">Visualizar Igreja</a></li>
    </ul>
    <hr>
    <livewire:pages.app.director.church.edit $church />
</x-layouts.app>
