<x-layouts.app :title="__('Editar Base de Treinamento')">
    <ul class="flex gap-6 flex-wrap">
        <li><a href="{{ route('app.director.church.view_host', $church) }}">Visualizar Base de Treinamentos</a></li>
    </ul>
    <hr>
    {{-- <livewire:pages.app.director.church.edit-host /> --}}
</x-layouts.app>
