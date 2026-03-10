<x-layouts.app :title="__('Listar Treinamentos')">
    <x-src.training-index
        role="teacher"
        :create-route="route('app.teacher.trainings.create')"
        :status-key="$statusKey"
        :statuses="$statuses"
        :groups="$groups"
        :filter-value="$filter"
    />
</x-layouts.app>
