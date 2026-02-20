<x-layouts.app :title="__('Saidas de Treinamento Pratico')">
    <livewire:pages.app.teacher.training.statistics :training="$training" />
    <livewire:pages.app.teacher.training.manage-mentors-modal :trainingId="$training->id"
        wire:key="manage-mentors-modal-statistics-{{ $training->id }}" />
    <livewire:pages.app.teacher.training.create-mentor-user-modal :trainingId="$training->id"
        wire:key="create-mentor-user-modal-statistics-{{ $training->id }}" />
</x-layouts.app>
