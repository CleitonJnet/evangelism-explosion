<x-layouts.app :title="__('Visualizar Igreja')">
    <ul class="flex gap-6 flex-wrap">
        <li><a href="{{ route('app.director.church.index') }}">Listar Igrejas</a></li>
        <li><a href="{{ route('app.director.church.edit', $church) }}">Editar Igreja</a></li>
        <li><a href="{{ route('app.director.church.profile.create', $church) }}">Novo Participante</a></li>
    </ul>
    <hr>
    <livewire:pages.app.director.church.view :church="$church" />
</x-layouts.app>
