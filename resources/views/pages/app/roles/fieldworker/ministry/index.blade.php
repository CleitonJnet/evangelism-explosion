<x-layouts.app :title="__('Listar Ministérios')">
    <ul class="flex gap-6 flex-wrap">
        <li><a href="{{ route('app.director.ministry.create') }}">Novo Ministério</a></li>
    </ul>
    <hr>
    <livewire:pages.app.director.ministry.index />
</x-layouts.app>
