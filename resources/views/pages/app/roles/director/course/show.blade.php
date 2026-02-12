<x-layouts.app :title="__('Visualizar Curso')">
    <ul class="flex gap-6 flex-wrap">
        <li><a href="{{ route('app.director.ministry.show', $ministry) }}">Visualizar Ministério</a></li>
        <li><a href="{{ route('app.director.ministry.course.edit', ['ministry' => $ministry, 'course' => $course]) }}">Editar
                Curso</a></li>
        </li>
    </ul>
    <hr>
    <livewire:pages.app.director.course.view :course="$course" />
</x-layouts.app>
