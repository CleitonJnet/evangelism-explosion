@php
    $training->loadMissing(['course.ministry', 'church']);

    $eventTitle = trim(
        implode(' ', array_filter([
            $training->course?->type,
            $training->course?->name,
        ])),
    );
    $ministryName = $training->course?->ministry?->name ?: __('Ministério não informado');
    $baseChurchName = $training->church?->name ?: __('Igreja base não informada');
@endphp

<x-layouts.app :title="__('Gerenciamento de inscrições')">
    <x-src.toolbar.header :title="__('Gerenciamento de inscrições')" :description="__(
        'Acompanhe e atualize os status dos inscritos deste evento, com os participantes agrupados por igreja.',
    )">
        <x-slot:right>
            <div class="hidden px-1 py-2 text-right md:block">
                <div class="text-sm font-bold text-slate-800">
                    {{ $eventTitle !== '' ? $eventTitle : __('Evento sem nome') }}
                </div>
                <div class="text-xs font-light text-slate-600">
                    {{ $ministryName }}
                </div>
                <div class="text-xs font-light text-slate-500">
                    {{ $baseChurchName }}
                </div>
            </div>
        </x-slot:right>
    </x-src.toolbar.header>
    <x-src.toolbar.nav :title="__('Gerenciamento de inscrições')" :description="__('Atualize comprovante, credenciamento e entrega de kit com poucos cliques.')" justify="justify-between">
        <div class="flex flex-wrap gap-2 items-center">
            <x-src.toolbar.button :href="route('app.teacher.trainings.show', $training)" :label="__('Detalhes do Evento')" icon="eye" :tooltip="__('Voltar para o treinamento')" />
        </div>
    </x-src.toolbar.nav>

    <livewire:pages.app.teacher.training.registrations :training="$training" />
</x-layouts.app>
