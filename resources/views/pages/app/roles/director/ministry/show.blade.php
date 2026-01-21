<x-layouts.app :title="__('Visualizar Ministérios')">
    <ul class="flex gap-6 flex-wrap">
        <li><a href="{{ route('app.director.ministry.index') }}">Lista de Ministérios</a></li>
        <li><a href="{{ route('app.director.ministry.edit', $ministry) }}">Editar Ministério</a></li>
        <li><a href="{{ route('app.director.ministry.course.create', $ministry) }}">Criar Curso</a></li>
    </ul>
    <hr>
    <livewire:pages.app.director.ministry.view :ministry="$ministry" />
</x-layouts.app>
