<x-layouts.app :title="__('Editar Curso')">
    <ul class="flex gap-6 flex-wrap">
        <li><a href="{{ route('app.director.ministry.show', $ministry) }}">Visualizar Ministério</a></li>
        <li><a href="{{ route('app.director.ministry.course.show', ['ministry' => $ministry, 'course' => $course]) }}">Visualizar
                Curso</a></li>
    </ul>
    <hr>
    <livewire:pages.app.director.course.edit :course="$course" />
</x-layouts.app>
