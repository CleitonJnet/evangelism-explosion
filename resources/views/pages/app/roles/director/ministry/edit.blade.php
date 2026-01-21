<x-layouts.app :title="__('Editar Ministério')">
    <ul class="flex gap-6 flex-wrap">
        <li><a href="{{ route('app.director.ministry.show', $ministry) }}">Visualizar Ministério</a></li>
    </ul>
    <hr>
    <livewire:pages.app.director.ministry.edit :ministry="$ministry->id" />
</x-layouts.app>
