<x-layouts.app :title="__('Evento no Portal Base')">
    <x-app.portal.training-shell :training="$training" :tabs="$tabs" :active-tab="$activeTab" :capabilities="$capabilities" :portal-capabilities="$portalCapabilities" :assignments="$assignments" :training-context="$trainingContext" :portal-label="$portalLabel" :portal-roles="$portalRoles" :area-cards="$areaCards" :report-summary="$reportSummary">
        <livewire:pages.app.portal.base.training.view :training="$training" />
    </x-app.portal.training-shell>
</x-layouts.app>
