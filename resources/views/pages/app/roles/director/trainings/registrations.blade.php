@php
    $training->loadMissing(['course.ministry', 'church']);

    $eventTitle = trim(implode(' ', array_filter([$training->course?->type, $training->course?->name])));
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
    <x-src.toolbar.nav>
        <x-src.toolbar.button :href="route('app.director.training.show', $training)" :label="__('Detalhes do treinamento')" icon="eye"
            :tooltip="__('Voltar para os detalhes do treinamento')" />
        <x-src.toolbar.button :href="route('app.director.inventory.index')" :label="__('Estoques')" icon="archive-box"
            :tooltip="__('Abrir o módulo de estoque do diretor')" />
    </x-src.toolbar.nav>
    <livewire:pages.app.director.training.registrations :training="$training" />
</x-layouts.app>
