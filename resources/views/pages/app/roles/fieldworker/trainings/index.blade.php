<x-layouts.app :title="__('Listar Treinamentos')">
    <ul class="flex gap-6 flex-wrap">
        <li><a href="{{ route('app.director.training.create') }}">Novo Treinamento</a></li>
    </ul>
    <hr>
    <livewire:pages.app.director.training.index />
</x-layouts.app>
