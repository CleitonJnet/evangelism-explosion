<x-layouts.app :title="__('Cadastrar Base de Treinamentos')">
    <ul class="flex gap-6 flex-wrap">
        <li><a href="{{ route('app.director.church.index') }}">Listar Igrejas</a></li>
        <li><a href="{{ route('app.director.church.edit_host', $church) }}">Editar Base de Treinamentos</a></li>
    </ul>
    <hr>
    <livewire:pages.app.director.church.view-host />
</x-layouts.app>
