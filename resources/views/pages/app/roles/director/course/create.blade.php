<x-layouts.app :title="__('Criar Curso')">
    <ul class="flex gap-6 flex-wrap">
        <li><a href="{{ route('app.director.ministry.show', $ministry) }}">Visualizar Ministério</a></li>
    </ul>
    <hr>
    <livewire:pages.app.director.course.create :ministry="$ministry" />
</x-layouts.app>
