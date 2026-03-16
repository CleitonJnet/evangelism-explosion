<x-layouts.app :title="__('Abordagens STP do evento')">
    <x-app.portal.training-shell :training="$training" :tabs="$tabs" :active-tab="$activeTab" :capabilities="$capabilities" :assignments="$assignments" :training-context="$trainingContext" :portal-label="$portalLabel" :portal-roles="$portalRoles">
        <livewire:pages.app.portal.base.training.stp-approaches-board :training="$training" />
    </x-app.portal.training-shell>
</x-layouts.app>
