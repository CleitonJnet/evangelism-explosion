<x-layouts.app :title="__('Leitura comparativa dos relatorios')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <x-app.portal.page-header
            eyebrow="Portal Staff"
            :title="$comparison['queue_item']['title']"
            :description="'Leitura cruzada entre relatorio da igreja-base e relatorio do professor, com registro de governanca e follow-up institucional.'"
            :breadcrumbs="[
                ['label' => 'Portais', 'url' => route('app.start')],
                ['label' => 'Staff / Governanca', 'url' => route('app.portal.staff.dashboard')],
                ['label' => 'Relatorios recebidos', 'url' => route('app.portal.staff.reports.index')],
                ['label' => 'Comparacao', 'current' => true],
            ]">
            <flux:button variant="outline" :href="route('app.portal.staff.reports.index')" wire:navigate>
                {{ __('Voltar para a fila') }}
            </flux:button>
        </x-app.portal.page-header>

        <livewire:pages.app.portal.staff.reports.show :training="$training" />
    </div>
</x-layouts.app>
