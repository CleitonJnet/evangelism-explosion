@php
    use App\Helpers\DayScheduleHelper;

    $training->loadMissing([
        'eventDates' => fn($query) => $query->orderBy('date')->orderBy('start_time'),
        'scheduleItems' => fn($query) => $query->orderBy('date')->orderBy('starts_at')->orderBy('position'),
    ]);

    $hasScheduleError = !DayScheduleHelper::hasAllDaysMatch($training->eventDates, $training->scheduleItems);
@endphp

<div x-data x-on:schedule-alert.window="window.alert($event.detail.message)" wire:loading.class="pointer-events-none"
    wire:target="regenerate,moveAfter,applyDuration,toggleDayBlock,addBreak,deleteBreak">
    <div class="absolute inset-0 opacity-0 pointer-events-all cursor-wait" wire:loading
        wire:target="regenerate,moveAfter,applyDuration,toggleDayBlock,addBreak,deleteBreak"></div>

    <x-src.toolbar.header :title="__('Programação do treinamento')" :description="__('Organize horários e sessões do treinamento selecionado.')" justify="justify-between" />
    <x-src.toolbar.nav :title="__('Programação do treinamento')" :description="__('Organize horários e sessões do treinamento selecionado.')" justify="justify-between">
        <div class="flex flex-wrap gap-2 items-center">
            <x-src.toolbar.button :href="route('app.teacher.trainings.show', $training)" :label="__('Detalhes do Evento')" icon="eye" :tooltip="__('Voltar para o Treinamento')" />
            <x-src.toolbar.button :href="'#'" :label="__('Datas do Evento')" icon="calendar" :tooltip="__('Editar dias e horários')"
                x-on:click.prevent="$dispatch('open-edit-event-dates-modal', { trainingId: {{ $training->id }} })" />
        </div>

        <label
            class="flex flex-wrap items-center justify-end gap-3 cursor-pointer hover:bg-sky-950/5 transition duration-200 rounded-lg"
            aria-label="{{ __('Regenerar agenda') }}">
            <span class="text-xs max-w-24 text-right p-1 select-none">
                {{ __('Reset para a agenda padrão') }}
            </span>
            <flux:button variant="primary" type="button" icon="arrow-path" tooltip="{{ __('Regenerar agenda') }}"
                aria-label="{{ __('Regenerar agenda') }}" x-on:click="$wire.regenerate()" wire:loading.attr="disabled"
                wire:target="regenerate" class="cursor-pointer" />
        </label>
    </x-src.toolbar.nav>

    @php
        $hasMultipleDays = $eventDates->count() > 1;
        $lastEventDate = $eventDates->last();
        $lastEventDateKey = $lastEventDate?->date;
        $lastEventEnd =
            $lastEventDate && $lastEventDate->end_time
                ? \Carbon\Carbon::parse($lastEventDate->date . ' ' . $lastEventDate->end_time)
                : null;
    @endphp

    @if ($hasMultipleDays)
        <x-src.toolbar.bar :breadcrumb="false" justify="justify-end">
            <div class="max-w-28 text-right text-xs">{{ __('Dias de Treinamentos') }}</div>
            @foreach ($eventDates as $itemDate)
                @php
                    $week = \App\Helpers\WeekHelper::dayName($itemDate->date);
                @endphp
                <x-src.toolbar.course-button :href="'#day' . $loop->iteration" :label="__($week)" :tooltip="__($week)" />
            @endforeach
        </x-src.toolbar.bar>
    @endif

    <section class="grid gap-6">
        @forelse ($eventDates as $eventDate)
            @php
                $dateKey = $eventDate->date;
                $items = $scheduleByDate->get($dateKey, collect())->sortBy('position');
                $planStatus =
                    $planStatusByDate[$dateKey] ?? \App\Livewire\Pages\App\Teacher\Training\Schedule::PLAN_STATUS_UNDER;
                $dayBorderClass =
                    $planStatus === \App\Livewire\Pages\App\Teacher\Training\Schedule::PLAN_STATUS_OVER
                        ? 'border-2 border-red-400'
                        : ($planStatus === \App\Livewire\Pages\App\Teacher\Training\Schedule::PLAN_STATUS_OK
                            ? 'border border-emerald-500'
                            : 'border-2 border-amber-400');
                $dayStart = \Carbon\Carbon::parse($eventDate->date . ' ' . $eventDate->start_time)->format(
                    'Y-m-d H:i:s',
                );
                $dayUiFlags = $dayUi[$dateKey] ?? [];
                $showBreakfast = (bool) ($dayUiFlags['showBreakfast'] ?? false);
                $showLunch = (bool) ($dayUiFlags['showLunch'] ?? false);
                $showSnack = (bool) ($dayUiFlags['showSnack'] ?? false);
                $showDinner = (bool) ($dayUiFlags['showDinner'] ?? false);
            @endphp
            <div id="{{ 'day' . $loop->iteration }}"
                class="rounded-2xl {{ $dayBorderClass }} bg-linear-to-br from-slate-100 via-white to-slate-200 p-4"
                wire:key="schedule-day-{{ $dateKey }}">
                <div>
                    <div class="flex flex-wrap items-start justify-between gap-2 pb-2">
                        <div class="grid gap-0.5">
                            <div class="flex flex-wrap items-center bg-sky-950/10 rounded">
                                <div
                                    class="flex flex-wrap items-center gap-2 rounded bg-sky-950 px-2 pt-1 pb-0.5 text-[11px] font-semibold uppercase tracking-wide text-amber-200">
                                    @if ($hasMultipleDays)
                                        <span class="">
                                            {{ __('Dia') }} {{ $loop->iteration }}:
                                        </span>
                                    @endif
                                    <div class="font-semibold text-heading text-slate-50">
                                        {{ \App\Helpers\WeekHelper::dayName($dateKey) }}
                                        -
                                        {{ \Carbon\Carbon::parse($dateKey)->format('d/m') }}
                                    </div>
                                </div>
                                <div class="text-xs text-amber-700 px-2">
                                    {{ $eventDate->start_time ? substr($eventDate->start_time, 0, 5) : '' }} -
                                    {{ $eventDate->end_time ? substr($eventDate->end_time, 0, 5) : '' }}
                                </div>
                            </div>
                            <div class="text-xs flex gap-4">
                                <label for="day_time_start_{{ $dateKey }}">
                                    <span>{{ __('Início das sessões') }}</span>
                                    <input type="time" id="day_time_start_{{ $dateKey }}" class=""
                                        wire:model.live.blur="dayTimes.{{ $dateKey }}.start_time"
                                        wire:loading.attr="disabled"
                                        wire:target="dayTimes.{{ $dateKey }}.start_time">
                                </label>
                                <label for="day_time_end_{{ $dateKey }}">
                                    <span>{{ __('Fim das sessões') }}</span>
                                    <input type="time" id="day_time_end_{{ $dateKey }}" class=""
                                        wire:model.live="dayTimes.{{ $dateKey }}.end_time"
                                        wire:loading.attr="disabled"
                                        wire:target="dayTimes.{{ $dateKey }}.end_time">
                                </label>
                            </div>
                        </div>
                        <div
                            class="flex flex-auto flex-wrap items-center justify-end gap-2 text-xs text-(--ee-app-muted)">
                            <x-app.switch-schedule :label="__('Boas-vindas')" :key="$dateKey" :checked="data_get($dayBlocks, $dateKey . '.welcome', true)"
                                wire:change="toggleDayBlock('{{ $dateKey }}', 'welcome', $event.target.checked)"
                                wire:loading.attr="disabled" wire:target="toggleDayBlock" />
                            <x-app.switch-schedule :label="__('Devocional')" :key="$dateKey" :checked="data_get($dayBlocks, $dateKey . '.devotional', true)"
                                wire:change="toggleDayBlock('{{ $dateKey }}', 'devotional', $event.target.checked)"
                                wire:loading.attr="disabled" wire:target="toggleDayBlock" />
                            @if ($showBreakfast)
                                <x-app.switch-schedule :label="__('Café')" :key="$dateKey" :checked="data_get($dayBlocks, $dateKey . '.breakfast', true)"
                                    wire:change="toggleDayBlock('{{ $dateKey }}', 'breakfast', $event.target.checked)"
                                    wire:loading.attr="disabled" wire:target="toggleDayBlock" />
                            @endif
                            @if ($showLunch)
                                <x-app.switch-schedule :label="__('Almoço')" :key="$dateKey" :checked="data_get($dayBlocks, $dateKey . '.lunch', true)"
                                    wire:change="toggleDayBlock('{{ $dateKey }}', 'lunch', $event.target.checked)"
                                    wire:loading.attr="disabled" wire:target="toggleDayBlock" />
                            @endif
                            @if ($showSnack)
                                <x-app.switch-schedule :label="__('Lanche')" :key="$dateKey" :checked="data_get($dayBlocks, $dateKey . '.snack', true)"
                                    wire:change="toggleDayBlock('{{ $dateKey }}', 'snack', $event.target.checked)"
                                    wire:loading.attr="disabled" wire:target="toggleDayBlock" />
                            @endif
                            @if ($showDinner)
                                <x-app.switch-schedule :label="__('Jantar')" :key="$dateKey" :checked="data_get($dayBlocks, $dateKey . '.dinner', true)"
                                    wire:change="toggleDayBlock('{{ $dateKey }}', 'dinner', $event.target.checked)"
                                    wire:loading.attr="disabled" wire:target="toggleDayBlock" />
                            @endif
                            <button type="button"
                                class="flex flex-col items-center justify-center gap-1 rounded-xl bg-slate-200 hover:bg-sky-200 transition duration-200 basis-20 py-1.5 cursor-pointer border border-slate-300 h-14"
                                wire:click="addBreak('{{ $dateKey }}')" wire:loading.attr="disabled"
                                wire:target="addBreak">
                                {{ __('Adicionar intervalo') }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl border border-(--ee-app-border) bg-white mb-1"
                    style="box-shadow: 0 0 2px 0 #052f4a">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs bg-linear-to-b from-sky-200 to-sky-300 uppercase text-(--ee-app-muted)">
                            <tr class="border-b border-(--ee-app-border)">
                                <th class="px-3 py-2 w-36">{{ __('Horário') }}</th>
                                <th class="px-3 py-2">{{ __('Sessão') }}</th>
                                <th class="px-3 py-2 w-36">{{ __('Duração') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-(--ee-app-border) js-schedule-day-list"
                            data-date-key="{{ $dateKey }}" data-day-start="{{ $dayStart }}">
                            @forelse ($items as $item)
                                @php
                                    $hasConflict = $item->status === 'CONFLICT';
                                    $tooltip = $hasConflict
                                        ? __('Conflito: sobreposição com item #:id', [
                                            'id' => $item->conflict_reason['with'] ?? '-',
                                        ])
                                        : '';
                                    $isOverflow = false;
                                    if ($lastEventDateKey && $lastEventEnd) {
                                        $itemDate = $item->date?->format('Y-m-d');
                                        if ($itemDate && $itemDate > $lastEventDateKey) {
                                            $isOverflow = true;
                                        } elseif (
                                            $itemDate === $lastEventDateKey &&
                                            $item->ends_at?->gt($lastEventEnd)
                                        ) {
                                            $isOverflow = true;
                                        }
                                    }
                                    $hour = (int) $item->starts_at->format('H');
                                    if ($hour < 12) {
                                        $periodClass = 'odd:bg-lime-100/30 even:bg-lime-100/40 hover:bg-lime-100';
                                    } elseif ($hour < 18) {
                                        $periodClass = 'odd:bg-amber-100/30 even:bg-amber-100/40 hover:bg-amber-100';
                                    } else {
                                        $periodClass = 'odd:bg-indigo-100/50 even:bg-indigo-100/65 hover:bg-indigo-100';
                                    }
                                @endphp
                                <tr class="items-center {{ $periodClass }} js-schedule-item group relative  {{ $item->section_id ? ' text-emerald-900 ' : ' text-sky-600 ' }}"
                                    wire:key="schedule-item-{{ $item->id }}" data-item-id="{{ $item->id }}"
                                    data-starts-at="{{ $item->starts_at->format('Y-m-d H:i:s') }}"
                                    data-ends-at="{{ optional($item->ends_at)->format('Y-m-d H:i:s') }}"
                                    :class="{
                                        'bg-red-50 text-red-700': {{ $hasConflict ? 'true' : 'false' }},
                                        'text-red-600': {{ $isOverflow ? 'true' : 'false' }},
                                        'opacity-70': busy,
                                    }"
                                    title="{{ $tooltip }}">
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <button type="button"
                                                class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-(--ee-app-border) bg-white text-(--ee-app-muted) transition hover:text-slate-700 hover:bg-sky-500 cursor-grab js-drag-handle"
                                                title="{{ __('Arrastar para reordenar') }}"
                                                aria-label="{{ __('Arrastar para reordenar') }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="3"
                                                        d="M8 6h.01M12 6h.01M16 6h.01M8 12h.01M12 12h.01M16 12h.01M8 18h.01M12 18h.01M16 18h.01" />
                                                </svg>
                                            </button>
                                            <span class="font-light text-xs">{{ $item->starts_at->format('H:i') }} -
                                                {{ $item->ends_at->format('H:i') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2 max-w-fit truncate md:max-w-auto">
                                        <div class="text-heading font-bold">
                                            {{ $item->title }}
                                        </div>
                                        @if ($item->section?->devotional)
                                            <div class="text-xs text-(--ee-app-muted)">
                                                {{ $item->section->devotional }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        @if ($item->type === 'SECTION' && $item->suggested_duration_minutes)
                                            @php
                                                $minDuration = (int) ceil($item->suggested_duration_minutes * 0.75);
                                                $maxDuration = (int) floor($item->suggested_duration_minutes * 1.25);
                                            @endphp
                                            <div class="flex items-center gap-2">
                                                <input type="number" min="{{ $minDuration }}"
                                                    max="{{ $maxDuration }}"
                                                    class="w-12 rounded-md border border-(--ee-app-border) text-right py-1 text-sm bg-white/60 focus-within:bg-white"
                                                    wire:model.blur="durationInputs.{{ $item->id }}"
                                                    wire:blur="applyDuration({{ $item->id }})"
                                                    wire:loading.attr="disabled" wire:target="applyDuration" />
                                                <div class="text-[10px] text-(--ee-app-muted) flex flex-col">
                                                    <div>
                                                        {{ __('de') }}<span class="font-bold">
                                                            {{ $minDuration }}-{{ $maxDuration }}
                                                        </span>
                                                    </div>
                                                    <div>{{ __('minutes') }}</div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="flex items-center gap-2">
                                                <input type="number" min="1" max="720"
                                                    class="w-12 rounded-md border border-(--ee-app-border) text-center md:text-right py-1 text-sm bg-white/60 focus-within:bg-white"
                                                    wire:model.blur="durationInputs.{{ $item->id }}"
                                                    wire:blur="applyDuration({{ $item->id }})"
                                                    wire:loading.attr="disabled" wire:target="applyDuration" />
                                                <span class="text-xs text-(--ee-app-muted)">
                                                    {{ __('minutes') }}
                                                </span>
                                                @if ($item->type === 'BREAK')
                                                    <button type="button"
                                                        class="hidden group-hover:inline-flex items-center justify-center transition duration-200 group-hover:text-red-700 group-hover:bg-red-50 absolute right-0 inset-y-0 w-fit h-full px-2 cursor-pointer"
                                                        title="{{ __('Excluir intervalo') }}"
                                                        wire:click="deleteBreak({{ $item->id }})"
                                                        wire:loading.attr="disabled" wire:target="deleteBreak">
                                                        <svg version="1.1" id="remove"
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            class="w-5 h-5 fill-red-500"
                                                            xmlns:xlink="http://www.w3.org/1999/xlink"
                                                            viewBox="0 0 459.739 459.739" xml:space="preserve">
                                                            <path
                                                                d="M229.869,0C102.917,0,0,102.917,0,229.869c0,126.952,102.917,229.869,229.869,229.869s229.869-102.917,229.869-229.869 C459.738,102.917,356.821,0,229.869,0z M313.676,260.518H146.063c-16.926,0-30.649-13.723-30.649-30.649 c0-16.927,13.723-30.65,30.649-30.65h167.613c16.925,0,30.649,13.723,30.649,30.65C344.325,246.795,330.601,260.518,313.676,260.518 z" />
                                                        </svg>
                                                    </button>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="px-3 py-4 text-xs text-(--ee-app-muted)" colspan="3">
                                        {{ __('Nenhum item para este dia.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @php
                    $planDiff = $planDiffByDate[$dateKey] ?? ['hours' => 0, 'minutes' => 0];
                @endphp
                @if ($planStatus === \App\Livewire\Pages\App\Teacher\Training\Schedule::PLAN_STATUS_UNDER)
                    <div class="text-sm text-amber-600 font-bold flex gap-0.5 items-end">
                        &#9888;
                        {{ __('A carga horária prevista para o dia ainda não foi totalmente preenchida') }}
                        ({{ __('até às') }}
                        {{ $eventDate->end_time ? substr($eventDate->end_time, 0, 5) : '' }})
                        - {{ $planDiff['hours'] }}h {{ $planDiff['minutes'] }}m.
                    </div>
                @elseif ($planStatus === \App\Livewire\Pages\App\Teacher\Training\Schedule::PLAN_STATUS_OVER)
                    <div class="text-sm text-red-600 font-bold flex gap-0.5 items-end">
                        &#10060;
                        {{ __('Uma ou mais sessões excederam o período previsto para o dia') }}
                        ({{ __('até às') }}
                        {{ $eventDate->end_time ? substr($eventDate->end_time, 0, 5) : '' }})
                        - {{ $planDiff['hours'] }}h {{ $planDiff['minutes'] }}m.
                    </div>
                @else
                    <div class="text-sm text-green-600 font-bold flex gap-0.5 items-end">
                        &#9989;
                        {{ __('O planejamento do dia atende plenamente à carga horária definida') }}
                        ({{ __('até às') }}
                        {{ $eventDate->end_time ? substr($eventDate->end_time, 0, 5) : '' }}).
                    </div>
                @endif
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-(--ee-app-border) p-6 text-sm text-(--ee-app-muted)">
                {{ __('Nenhuma data cadastrada para este treinamento.') }}
            </div>
        @endforelse
    </section>

    <livewire:pages.app.teacher.training.edit-event-dates-modal :trainingId="$training->id"
        wire:key="edit-event-dates-modal-{{ $training->id }}" />

</div>


{{--


Horário	Sessão	Duração

18:30 - 19:00
Boas-vindas
30
minutes

19:00 - 19:10
Devocional
10
minutes

19:10 - 19:45
Unidade 1: Por Que Estamos Aqui?
35
de 30-50
minutes

19:45 - 20:05
Unidade 2: Aprendendo a Explicação do Evangelho Em Sua Mão
20
de 15-25
minutes

20:05 - 20:15
Intervalo
10
minutes

20:15 - 20:50
Unidade 3: Vencendo o Medo de Testemunhar
35
de 30-50
minutes

20:50 - 21:30
Unidade 4: Encontrando Pessoas Receptivas
40
de 30-50
minutes
✅ O planejamento do dia atende plenamente à carga horária definida (até às 21:30).
Dia 2:
Sábado - 07/02
08:30 - 21:45
Horário de início das sessões
08:30
Horário de fim das sessões
21:45





Adicionar intervalo
Horário	Sessão	Duração

08:30 - 08:40
Devocional
10
minutes

08:40 - 09:20
Unidade 5: Compartilhando o Evangelho Através de Ilustrações
40
de 27-43
minutes

09:20 - 09:55
Unidade 6: Levando a Uma Decisão
35
de 23-37
minutes

09:55 - 10:20
Unidade 7: Desenvolvendo Amor pelos Perdidos
25
de 15-25
minutes

10:20 - 10:35
Intervalo
15
minutes

10:35 - 11:00
Unidade 8: Desenvolvendo um Ministério Contínuo de Evangelismo e Discipulado
25
de 15-25
minutes

11:00 - 11:40
Encerramento: Juntos na Colheita do Senhor
40
de 30-50
minutes

11:40 - 11:50
Intervalo
10
minutes

11:50 - 12:00
Orientações da Clínica de Evangelismo Explosivo
10
de 8-12
minutes

12:00 - 13:20
Almoço
80
minutes

13:20 - 13:40
O Treinamento de e² — Evangelismo Eficaz
20
de 15-25
minutes

13:40 - 14:15
Elementos-Chave do Treinamento e²
35
de 30-50
minutes

14:15 - 14:45
O Ensino Semanal no e²
Devocional Semanal
30
de 23-37
minutes

14:45 - 15:25
AULA: Saídas de Treinamento Prático (STP)
40
de 34-56
minutes

15:25 - 15:55
Lanche
30
minutes

15:55 - 16:15
Uso do Questionário de Segurança
20
de 15-25
minutes

16:15 - 16:25
ORIENTAÇÕES PARA PRÁTICA 1: Saídas de Treinamento Prático (STP)
10
de 8-12
minutes

16:25 - 17:25
PRÁTICA 1: Saídas de Treinamento Prático (STP)
60
de 45-75
minutes

17:25 - 17:55
Relatório Público
30
de 23-37
minutes

17:55 - 18:35
PRÁTICA 1: Relatório Público
40
de 30-50
minutes

18:35 - 19:35
Jantar
60
minutes

19:35 - 20:35
Unidade 1 — Conectando
Conectando-se às Pessoas
60
de 45-75
minutes

20:35 - 21:05
Unidade 2 — O Evangelho: Graça
A Graça de Deus
30
de 27-43
minutes

21:05 - 21:15
ORIENTAÇÕES PARA PRÁTICA 2: Saídas de Treinamento Prático (STP)
10
de 8-12
minutes

21:15 - 21:45
Unidade 3 — O Evangelho: Homem
A Condição do Homem
30
de 27-43
minutes
✅ O planejamento do dia atende plenamente à carga horária definida (até às 21:45).
Dia 3:
Domingo - 08/02
08:30 - 17:30
Horário de início das sessões
08:30
Horário de fim das sessões
17:30




Adicionar intervalo
Horário	Sessão	Duração

08:30 - 08:45
Devocional
15
minutes

08:45 - 09:15
Unidade 4 — Compartilhando Seu Testemunho
O Poder de Seu Testemunho
30
de 27-43
minutes

09:15 - 09:45
Unidade 5 — O Evangelho: Deus e Cristo
Deus e Jesus Cristo
30
de 27-43
minutes

09:45 - 10:15
Unidade 6 — O Evangelho: Fé
Entendendo a Fé
30
de 27-43
minutes

10:15 - 10:25
Intervalo
10
minutes

10:25 - 11:35
PRÁTICA 2: Saídas de Treinamento Prático (STP)
70
de 68-112
minutes

11:35 - 12:15
PRÁTICA 2: Relatório Público
40
de 30-50
minutes

12:15 - 13:30
Almoço
75
minutes

13:30 - 14:00
Unidade 7 — Decisão e Acompanhamento
O Amor de Deus pelos Perdidos
30
de 27-43
minutes

14:00 - 14:10
ORIENTAÇÕES PARA PRÁTICA 3: Saídas de Treinamento Prático (STP)
10
de 8-12
minutes

14:10 - 15:10
Guia de Implementação do Ministério de Evangelismo Explosivo
60
de 45-75
minutes

15:10 - 15:40
Lanche
30
minutes

15:40 - 16:50
PRÁTICA 3: Saídas de Treinamento Prático (STP)
70
de 60-100
minutes

16:50 - 17:30
PRÁTICA 3: Relatório Público
40
de 30-50
minutes

17:30 - 18:40
PRÁTICA 2: Saídas de Treinamento Prático (STP)
70
de 68-112
minutes

18:40 - 19:40
PRÁTICA 3: Saídas de Treinamento Prático (STP)
60
de 60-100
minutes

--}}
