<x-layouts.app :title="__('Gerenciamento de inscrições')">
    <x-src.toolbar.header :title="__('Gerenciamento de inscrições')" :description="__(
        'Acompanhe e atualize os status dos inscritos deste evento, com os participantes agrupados por igreja.',
    )" />
    <x-src.toolbar.nav :title="__('Gerenciamento de inscrições')" :description="__('Atualize comprovante, credenciamento e entrega de kit com poucos cliques.')" justify="justify-between">
        <div class="flex flex-wrap gap-2 items-center">
            <x-src.toolbar.button :href="route('app.teacher.trainings.show', $training)" :label="__('Detalhes do Evento')" icon="eye" :tooltip="__('Voltar para o treinamento')" />
        </div>
    </x-src.toolbar.nav>

    <livewire:pages.app.teacher.training.registrations :training="$training" />
</x-layouts.app>
