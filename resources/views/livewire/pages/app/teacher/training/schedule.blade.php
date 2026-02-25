@php
    use App\Helpers\DayScheduleHelper;

    $training->loadMissing([
        'eventDates' => fn($query) => $query->orderBy('date')->orderBy('start_time'),
        'scheduleItems' => fn($query) => $query->orderBy('date')->orderBy('starts_at')->orderBy('position'),
    ]);

    $hasScheduleError = !DayScheduleHelper::hasAllDaysMatch($training->eventDates, $training->scheduleItems);
@endphp

<div x-data="{ showRegenerateScheduleModal: false, showScheduleAttentionModal: @js($showScheduleAttentionModal) }" x-on:schedule-alert.window="window.alert($event.detail.message)"
    wire:loading.class="pointer-events-none"
    wire:target="regenerate,confirmScheduleAttentionAndGenerateDefault,moveAfter,applyDuration,toggleDayBlock,addBreak,deleteBreak">
    <div class="absolute inset-0 opacity-0 pointer-events-all cursor-wait" wire:loading
        wire:target="regenerate,confirmScheduleAttentionAndGenerateDefault,moveAfter,applyDuration,toggleDayBlock,addBreak,deleteBreak">
    </div>

    <x-src.toolbar.header :title="__('Programação do treinamento')" :description="__('Organize horários e sessões do treinamento selecionado.')" justify="justify-between" />
    <x-src.toolbar.nav :title="__('Programação do treinamento')" :description="__('Organize horários e sessões do treinamento selecionado.')" justify="justify-between">
        <div class="flex flex-wrap gap-2 items-center">
            <x-src.toolbar.button :href="route('app.teacher.trainings.show', $training)" :label="__('Detalhes do Evento')" icon="eye" :tooltip="__('Voltar para o Treinamento')" />
            <x-src.toolbar.button :href="'#'" :label="__('Datas do Evento')" icon="calendar" :tooltip="__('Editar dias e horários')"
                x-on:click.prevent="$dispatch('open-edit-event-dates-modal', { trainingId: {{ $training->id }} })" />
        </div>

        <div class="flex items-center gap-2 overflow-auto">
            <label
                class="flex items-center justify-end gap-3 cursor-pointer bg-sky-900/5 hover:bg-sky-900/75 hover:text-white transition rounded-lg"
                aria-label="{{ __('Regenerar programação do evento') }}">
                <span class="text-xs max-w-24 text-right p-1 select-none">
                    {{ __('Redefinir programação') }}
                </span>
                <flux:button variant="primary" type="button" icon="arrow-path"
                    tooltip="{{ __('Regenerar programação do evento') }}"
                    aria-label="{{ __('Regenerar programação do evento') }}"
                    x-on:click="showRegenerateScheduleModal = true" wire:loading.attr="disabled"
                    wire:target="regenerate" class="cursor-pointer" />
            </label>

            <span class="mx-1 h-7 w-px bg-slate-300/80"></span>

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
                <div class="max-w-24 min-w-20 text-right text-xs">{{ __('Ínidice de dias do evento') }}</div>
                @foreach ($eventDates as $itemDate)
                    @php
                        $week = \App\Helpers\WeekHelper::dayName($itemDate->date);
                    @endphp
                    <x-src.toolbar.course-button :href="'#day' . $loop->iteration" :label="__($week)" :tooltip="__($week)" />
                @endforeach
            @endif

        </div>
    </x-src.toolbar.nav>


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
                    <div class="overflow-auto p-0" style="max-width: calc(100vw - 80px)">
                        <table class="w-full text-left text-sm">
                            <thead
                                class="text-xs bg-linear-to-b from-sky-200 to-sky-300 uppercase text-(--ee-app-muted)">
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
                                            $periodClass =
                                                'odd:bg-amber-100/30 even:bg-amber-100/40 hover:bg-amber-100';
                                        } else {
                                            $periodClass =
                                                'odd:bg-indigo-100/50 even:bg-indigo-100/65 hover:bg-indigo-100';
                                        }
                                    @endphp
                                    <tr class="items-center {{ $periodClass }} js-schedule-item group relative  {{ $item->section_id ? ' text-emerald-900 ' : ' text-sky-600 ' }}"
                                        wire:key="schedule-item-{{ $item->id }}"
                                        data-item-id="{{ $item->id }}"
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
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="3"
                                                            d="M8 6h.01M12 6h.01M16 6h.01M8 12h.01M12 12h.01M16 12h.01M8 18h.01M12 18h.01M16 18h.01" />
                                                    </svg>
                                                </button>
                                                <span class="font-light text-xs">{{ $item->starts_at->format('H:i') }}
                                                    -
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
                                            @php
                                                $itemBounds = $durationBounds[$item->id] ?? ['min' => 5, 'max' => 720];
                                                $minDuration = (int) ($itemBounds['min'] ?? 5);
                                                $maxDuration = (int) ($itemBounds['max'] ?? 720);
                                                $rangeWarning = (bool) ($durationRangeWarnings[$item->id] ?? false);
                                                $currentDuration = (int) ($durationInputs[$item->id] ?? ($item->planned_duration_minutes ?: $item->suggested_duration_minutes ?? 60));
                                                $rangeColorClass = 'text-green-600';

                                                if ($currentDuration < $minDuration) {
                                                    $rangeColorClass = 'text-amber-600';
                                                } elseif ($currentDuration > $maxDuration) {
                                                    $rangeColorClass = 'text-red-600';
                                                }
                                            @endphp
                                            @if ($item->type === 'SECTION' && $item->suggested_duration_minutes)
                                                <div class="flex items-center gap-2">
                                                    <div x-data="{
                                                        step(delta) {
                                                            const input = this.$refs.durationInput;
                                                            let value = parseInt(input.value, 10);
                                                            if (Number.isNaN(value)) {
                                                                value = {{ (int) ($durationInputs[$item->id] ?? ($item->planned_duration_minutes ?: $item->suggested_duration_minutes ?? 60)) }};
                                                            }
                                                            value = Math.max({{ $minDuration }}, Math.min({{ $maxDuration }}, value + delta));
                                                            value = Math.round(value / 5) * 5;
                                                            value = Math.max({{ $minDuration }}, Math.min({{ $maxDuration }}, value));
                                                            input.value = String(value);
                                                            input.dispatchEvent(new Event('input', { bubbles: true }));
                                                        }
                                                    }"
                                                        class="flex items-center rounded-md border border-(--ee-app-border) bg-white/60">
                                                        <button type="button"
                                                            class="inline-flex h-8 w-8 items-center justify-center text-slate-600 transition hover:bg-slate-100 disabled:opacity-50"
                                                            x-on:click="step(-5)"
                                                            aria-label="{{ __('Reduzir duração') }}">
                                                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                                viewBox="0 0 20 20" class="h-3 w-3 fill-current">
                                                                <path d="M10 14 4 6h12l-6 8Z" />
                                                            </svg>
                                                        </button>
                                                        <input type="text" inputmode="numeric" pattern="[0-9]*"
                                                            x-ref="durationInput"
                                                            x-on:input="
                                                                $el.value = $el.value.replace(/[^0-9]/g, '');
                                                                if ($el.value === '') { return; }
                                                                const value = parseInt($el.value, 10);
                                                                if (!Number.isNaN(value)) {
                                                                    let roundedValue = Math.round(value / 5) * 5;
                                                                    roundedValue = Math.max({{ $minDuration }}, Math.min({{ $maxDuration }}, roundedValue));
                                                                    $el.value = String(roundedValue);
                                                                }
                                                            "
                                                            class="w-14 border-x border-(--ee-app-border) py-1 text-center text-sm bg-white focus-within:bg-white"
                                                            wire:model.live.debounce.700ms="durationInputs.{{ $item->id }}"
                                                            wire:loading.attr="disabled"
                                                            wire:target="durationInputs.{{ $item->id }}" />
                                                        <button type="button"
                                                            class="inline-flex h-8 w-8 items-center justify-center text-slate-600 transition hover:bg-slate-100 disabled:opacity-50"
                                                            x-on:click="step(5)"
                                                            aria-label="{{ __('Aumentar duração') }}">
                                                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                                viewBox="0 0 20 20" class="h-3 w-3 fill-current">
                                                                <path d="M10 6 4 14h12L10 6Z" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                    <div class="text-[10px] flex flex-col {{ $rangeColorClass }}">
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
                                                    <div x-data="{
                                                        step(delta) {
                                                            const input = this.$refs.durationInput;
                                                            let value = parseInt(input.value, 10);
                                                            if (Number.isNaN(value)) {
                                                                value = {{ (int) ($durationInputs[$item->id] ?? ($item->planned_duration_minutes ?: $item->suggested_duration_minutes ?? 60)) }};
                                                            }
                                                            value = Math.max(5, Math.min(720, value + delta));
                                                            value = Math.round(value / 5) * 5;
                                                            value = Math.max(5, Math.min(720, value));
                                                            input.value = String(value);
                                                            input.dispatchEvent(new Event('input', { bubbles: true }));
                                                        }
                                                    }"
                                                        class="flex items-center rounded-md border border-(--ee-app-border) bg-white/60">
                                                        <button type="button"
                                                            class="inline-flex h-8 w-8 items-center justify-center text-slate-600 transition hover:bg-slate-100 disabled:opacity-50"
                                                            x-on:click="step(-5)"
                                                            aria-label="{{ __('Reduzir duração') }}">
                                                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                                viewBox="0 0 20 20" class="h-3 w-3 fill-current">
                                                                <path d="M10 14 4 6h12l-6 8Z" />
                                                            </svg>
                                                        </button>
                                                        <input type="text" inputmode="numeric" pattern="[0-9]*"
                                                            x-ref="durationInput"
                                                            x-on:input="
                                                                $el.value = $el.value.replace(/[^0-9]/g, '');
                                                                if ($el.value === '') { return; }
                                                                const value = parseInt($el.value, 10);
                                                                if (!Number.isNaN(value)) {
                                                                    let roundedValue = Math.round(value / 5) * 5;
                                                                    roundedValue = Math.max(5, Math.min(720, roundedValue));
                                                                    $el.value = String(roundedValue);
                                                                }
                                                            "
                                                            class="w-14 border-x border-(--ee-app-border) py-1 text-center text-sm bg-white focus-within:bg-white"
                                                            wire:model.live.debounce.700ms="durationInputs.{{ $item->id }}"
                                                            wire:loading.attr="disabled"
                                                            wire:target="durationInputs.{{ $item->id }}" />
                                                        <button type="button"
                                                            class="inline-flex h-8 w-8 items-center justify-center text-slate-600 transition hover:bg-slate-100 disabled:opacity-50"
                                                            x-on:click="step(5)"
                                                            aria-label="{{ __('Aumentar duração') }}">
                                                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                                viewBox="0 0 20 20" class="h-3 w-3 fill-current">
                                                                <path d="M10 6 4 14h12L10 6Z" />
                                                            </svg>
                                                        </button>
                                                    </div>
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

    @if ($showScheduleAttentionModal)
        <div x-cloak x-show="showScheduleAttentionModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4">
            <div class="w-full max-w-2xl rounded-2xl border border-(--ee-app-border) bg-white p-5 shadow-2xl md:p-6">
                <div class="space-y-3">
                    <h3 class="text-lg font-semibold text-slate-900">
                        {{ __('Atenção aos horários das sessões') }}
                    </h3>
                    <p class="text-sm text-slate-700">
                        {{ __('Revise e ajuste os horários e a duração de cada sessão conforme o Manual de Atividades, garantindo tempo adequado para cada aula e para a realidade da igreja.') }}
                    </p>
                </div>

                <div class="mt-6 flex justify-end">
                    @if ($training->scheduleItems->isEmpty())
                        <x-src.btn-gold type="button" :label="__('Entendi e gerar programação padrão')"
                            x-on:click="showScheduleAttentionModal = false"
                            wire:click="confirmScheduleAttentionAndGenerateDefault" wire:loading.attr="disabled"
                            wire:target="confirmScheduleAttentionAndGenerateDefault" />
                    @else
                        <x-src.btn-gold type="button" :label="__('Entendi e vou ajustar')"
                            x-on:click="showScheduleAttentionModal = false" />
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div x-cloak x-show="showRegenerateScheduleModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4"
        x-on:keydown.escape.window="
            showRegenerateScheduleModal = false"
        x-on:click.self="showRegenerateScheduleModal = false">
        <div class="w-full max-w-2xl rounded-2xl border border-(--ee-app-border) bg-white p-5 shadow-2xl md:p-6">
            <div class="space-y-3">
                <h3 class="text-lg font-semibold text-slate-900">
                    {{ __('Confirmar regeneração da programação') }}
                </h3>
                <p class="text-sm text-slate-700">
                    {{ __('Ao confirmar esta ação, todos os horários e sessões serão reajustados para a ordem padrão das unidades do treinamento.') }}
                </p>
                <p class="text-sm text-slate-700">
                    {{ __('Depois da regeneração, será necessário revisar e ajustar os horários de cada sessão para o tempo mais adequado à realidade da igreja.') }}
                </p>
            </div>

            <div class="mt-6 flex flex-wrap justify-end gap-2">
                <x-src.btn-silver type="button" :label="__('Cancelar')"
                    x-on:click="showRegenerateScheduleModal = false" />
                <x-src.btn-gold type="button" :label="__('Confirmar e regenerar')" x-on:click="showRegenerateScheduleModal = false"
                    wire:click="regenerate" wire:loading.attr="disabled" wire:target="regenerate" />
            </div>
        </div>
    </div>

</div>
