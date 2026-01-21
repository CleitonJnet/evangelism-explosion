<x-layouts.app :title="__('Listar Igrejas')">

    <ul class="flex gap-6 flex-wrap">
        <li><a href="{{ route('app.director.church.create') }}">Nova Igreja</a></li>
        <li><a href="{{ route('app.director.church.make_host') }}">Nova Base de Treinamentos</a></li>
    </ul>
    <hr>

    <div class="grid gap-4 grid-cols-2">
        <livewire:pages.app.director.church.hosts />
        <livewire:pages.app.director.church.index />
    </div>

</x-layouts.app>
