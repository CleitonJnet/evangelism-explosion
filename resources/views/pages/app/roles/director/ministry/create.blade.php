<x-layouts.app :title="__('Criar Ministério')">
    <ul class="flex gap-6 flex-wrap">
        <li><a href="{{ route('app.director.ministry.index') }}">Lista Ministérios</a></li>
    </ul>
    <hr>
    <livewire:pages.app.director.ministry.create />
</x-layouts.app>
