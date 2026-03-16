<x-layouts.app :title="__('Estatisticas do evento')">
    <x-app.portal.training-shell :training="$training" :tabs="$tabs" :active-tab="$activeTab" :capabilities="$capabilities" :assignments="$assignments" :training-context="$trainingContext" :portal-label="$portalLabel" :portal-roles="$portalRoles">
        <livewire:pages.app.portal.base.training.statistics :training="$training" />
        <livewire:pages.app.teacher.training.manage-mentors-modal :trainingId="$training->id" wire:key="portal-base-manage-mentors-{{ $training->id }}" />
        <livewire:pages.app.teacher.training.create-mentor-user-modal :trainingId="$training->id" wire:key="portal-base-create-mentor-user-{{ $training->id }}" />
    </x-app.portal.training-shell>
</x-layouts.app>
