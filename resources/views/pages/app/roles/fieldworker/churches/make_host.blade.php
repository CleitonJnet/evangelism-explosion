<x-layouts.app :title="__('Cadastrar Base de Treinamentos')">
    <ul class="flex gap-6 flex-wrap">
        <li><a href="{{ route('app.director.church.index') }}">Listar Igrejas</a></li>
    </ul>
    <hr>
    <livewire:pages.app.director.church.make-host />
</x-layouts.app>
