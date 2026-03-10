<x-layouts.app :title="__('Listar Treinamentos')">
    <x-src.training-index
        role="director"
        :create-route="route('app.director.training.create')"
        :status-key="$statusKey"
        :statuses="$statuses"
        :groups="$groups"
        :filter-value="$filter"
    />
</x-layouts.app>
