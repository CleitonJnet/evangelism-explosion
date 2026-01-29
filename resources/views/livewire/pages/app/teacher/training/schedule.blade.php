<div x-data="trainingScheduleBoard(@entangle('modalOpen'))" x-on:schedule-alert.window="window.alert($event.detail.message)" class="space-y-6">
    <x-src.toolbar.bar :title="__('Programação do treinamento')" :description="__('Organize horários e sessões do treinamento selecionado.')">
        <x-src.toolbar.button :href="route('app.teacher.training.index')" :label="__('Listar todos')" icon="list" :tooltip="__('Lista de treinamentos')" />
        <x-src.toolbar.button :href="route('app.teacher.training.show', $training)" :label="__('Detalhes')" icon="eye" :tooltip="__('Detalhes do treinamento')" />
        <x-src.toolbar.button :href="route('app.teacher.training.edit', $training)" :label="__('Editar')" icon="pencil" :tooltip="__('Editar treinamento')" />
        <x-src.toolbar.button :href="route('app.teacher.training.schedule', $training)" :label="__('Programação')" icon="calendar" :active="true"
            :tooltip="__('Programação do evento')" />
    </x-src.toolbar.bar>
    <section
        class="rounded-2xl border border-[color:var(--ee-app-border)] bg-linear-to-br from-slate-100 via-white to-slate-200 p-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <flux:heading size="sm" level="2">{{ __('Agenda do treinamento') }}</flux:heading>
                <flux:text class="text-sm text-[color:var(--ee-app-muted)]">
                    {{ $training->course?->name ?? __('Curso não definido') }}
                </flux:text>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <select wire:model="mode"
                    class="rounded-md border border-[color:var(--ee-app-border)] bg-white px-3 py-2 text-sm">
                    <option value="AUTO_ONLY">{{ __('Regenerar automático') }}</option>
                    <option value="FULL">{{ __('Regenerar tudo') }}</option>
                </select>
                <flux:button variant="primary" type="button" icon="arrow-path" tooltip="{{ __('Regenerar agenda') }}"
                    aria-label="{{ __('Regenerar agenda') }}" x-on:click="regenerate" x-bind:disabled="busy" />
            </div>
        </div>
    </section>

    <section
        class="rounded-2xl border border-[color:var(--ee-app-border)] bg-linear-to-br from-slate-50 via-white to-slate-100 p-6">
        <flux:heading size="sm" level="2">{{ __('Configurações por dia') }}</flux:heading>
        <flux:text class="text-sm text-[color:var(--ee-app-muted)]">
            {{ __('Ajuste as chaves no cabeçalho de cada dia do treinamento.') }}
        </flux:text>
    </section>

    @php
        $hasMultipleDays = $eventDates->count() > 1;
    @endphp
    <section class="grid gap-6">
        @forelse ($eventDates as $eventDate)
            @php
                $dateKey = $eventDate->date;
                $items = $scheduleByDate->get($dateKey, collect())->sortBy('starts_at');
                $dayStart = \Carbon\Carbon::parse($eventDate->date . ' ' . $eventDate->start_time)->format(
                    'Y-m-d H:i:s',
                );
                $dinnerLabel = data_get($scheduleSettings, "days.$dateKey.meals.dinner.substitute_snack")
                    ? __('Lanche')
                    : __('Jantar');
                $dayStartTime = $eventDate->start_time
                    ? \Carbon\Carbon::parse($eventDate->date . ' ' . $eventDate->start_time)
                    : null;
                $dayEndTime = $eventDate->end_time
                    ? \Carbon\Carbon::parse($eventDate->date . ' ' . $eventDate->end_time)
                    : null;
                $isWithinWindow = function (
                    ?Carbon\Carbon $start,
                    ?Carbon\Carbon $end,
                    string $windowStart,
                    string $windowEnd,
                ): bool {
                    if (!$start || !$end) {
                        return false;
                    }

                    $windowStartTime = \Carbon\Carbon::parse($start->format('Y-m-d') . ' ' . $windowStart);
                    $windowEndTime = \Carbon\Carbon::parse($start->format('Y-m-d') . ' ' . $windowEnd);

                    return $end->gt($windowStartTime) && $start->lt($windowEndTime);
                };
                $showBreakfast = $isWithinWindow($dayStartTime, $dayEndTime, '07:00:00', '10:30:00');
                $showLunch = $isWithinWindow($dayStartTime, $dayEndTime, '10:00:00', '15:00:00');
                $showSnack = $isWithinWindow($dayStartTime, $dayEndTime, '14:00:00', '17:00:00');
                $showDinner = $isWithinWindow($dayStartTime, $dayEndTime, '17:00:00', '21:00:00');
            @endphp
            <div class="rounded-2xl border border-[color:var(--ee-app-border)] bg-linear-to-br from-slate-100 via-white to-slate-200 p-4"
                wire:key="schedule-day-{{ $dateKey }}"
                @drop.prevent="dropOn('{{ $dateKey }}', '{{ $dayStart }}')">
                <div class="mb-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex gap-2 items-center">
                            <div class="flex flex-wrap items-center gap-2 border-r-2 border-sky-950 pr-2">
                                @if ($hasMultipleDays)
                                    <span
                                        class="rounded bg-sky-950 px-2 pt-1 pb-0.5 text-[11px] font-semibold uppercase tracking-wide text-amber-200">
                                        {{ __('Dia') }} {{ $loop->iteration }}
                                    </span>
                                @endif
                                <div class="text-sm font-semibold text-heading">
                                    {{ \Carbon\Carbon::parse($dateKey)->format('d/m/Y') }}
                                </div>
                            </div>
                            <div class="text-xs text-[color:var(--ee-app-muted)]">
                                {{ $eventDate->start_time }} - {{ $eventDate->end_time }}
                            </div>
                        </div>
                        <flux:button size="sm" type="button" variant="outline" icon="plus"
                            tooltip="{{ __('Adicionar sessão') }}" aria-label="{{ __('Adicionar sessão') }}"
                            wire:click="openCreate('{{ $dateKey }}', '{{ substr($eventDate->start_time ?? '', 0, 5) }}')" />
                    </div>
                    <div
                        class="mt-3 flex flex-wrap items-center justify-end py-2 gap-3 text-xs text-[color:var(--ee-app-muted)]">
                        <div class="flex items-center gap-2">
                            <span>{{ __('Boas-vindas') }}</span>
                            <label class="relative inline-flex items-center">
                                <input type="checkbox"
                                    class="peer sr-only focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0"
                                    wire:model="scheduleSettings.days.{{ $dateKey }}.welcome_enabled"
                                    wire:change="saveDaySettings('{{ $dateKey }}')" />
                                <span
                                    class="h-5 w-9 rounded-full bg-slate-200 transition peer-checked:bg-sky-950"></span>
                                <span
                                    class="absolute left-1 top-1 h-3 w-3 rounded-full bg-amber-400 transition peer-checked:translate-x-4"></span>
                            </label>
                        </div>
                        <div class="flex items-center gap-2">
                            <span>{{ __('Devocional') }}</span>
                            <label class="relative inline-flex items-center">
                                <input type="checkbox"
                                    class="peer sr-only focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0"
                                    wire:model="scheduleSettings.days.{{ $dateKey }}.devotional_enabled"
                                    wire:change="saveDaySettings('{{ $dateKey }}')" />
                                <span
                                    class="h-5 w-9 rounded-full bg-slate-200 transition peer-checked:bg-sky-950"></span>
                                <span
                                    class="absolute left-1 top-1 h-3 w-3 rounded-full bg-amber-400 transition peer-checked:translate-x-4"></span>
                            </label>
                        </div>
                        @if ($showBreakfast)
                            <div class="flex items-center gap-2">
                                <span>{{ __('Café') }}</span>
                                <label class="relative inline-flex items-center">
                                    <input type="checkbox"
                                        class="peer sr-only focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0"
                                        wire:model="scheduleSettings.days.{{ $dateKey }}.meals.breakfast.enabled"
                                        wire:change="saveDaySettings('{{ $dateKey }}')" />
                                    <span
                                        class="h-5 w-9 rounded-full bg-slate-200 transition peer-checked:bg-sky-950"></span>
                                    <span
                                        class="absolute left-1 top-1 h-3 w-3 rounded-full bg-amber-400 transition peer-checked:translate-x-4"></span>
                                </label>
                            </div>
                        @endif
                        @if ($showLunch)
                            <div class="flex items-center gap-2">
                                <span>{{ __('Almoço') }}</span>
                                <label class="relative inline-flex items-center">
                                    <input type="checkbox"
                                        class="peer sr-only focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0"
                                        wire:model="scheduleSettings.days.{{ $dateKey }}.meals.lunch.enabled"
                                        wire:change="saveDaySettings('{{ $dateKey }}')" />
                                    <span
                                        class="h-5 w-9 rounded-full bg-slate-200 transition peer-checked:bg-sky-950"></span>
                                    <span
                                        class="absolute left-1 top-1 h-3 w-3 rounded-full bg-amber-400 transition peer-checked:translate-x-4"></span>
                                </label>
                            </div>
                        @endif
                        @if ($showSnack)
                            <div class="flex items-center gap-2">
                                <span>{{ __('Lanche da tarde') }}</span>
                                <label class="relative inline-flex items-center">
                                    <input type="checkbox"
                                        class="peer sr-only focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0"
                                        wire:model="scheduleSettings.days.{{ $dateKey }}.meals.afternoon_snack.enabled"
                                        wire:change="saveDaySettings('{{ $dateKey }}')" />
                                    <span
                                        class="h-5 w-9 rounded-full bg-slate-200 transition peer-checked:bg-sky-950"></span>
                                    <span
                                        class="absolute left-1 top-1 h-3 w-3 rounded-full bg-amber-400 transition peer-checked:translate-x-4"></span>
                                </label>
                            </div>
                        @endif
                        @if ($showDinner)
                            <div class="flex items-center gap-2">
                                <span>{{ $dinnerLabel }}</span>
                                <label class="relative inline-flex items-center">
                                    <input type="checkbox"
                                        class="peer sr-only focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0"
                                        wire:model="scheduleSettings.days.{{ $dateKey }}.meals.dinner.enabled"
                                        wire:change="saveDaySettings('{{ $dateKey }}')" />
                                    <span
                                        class="h-5 w-9 rounded-full bg-slate-200 transition peer-checked:bg-sky-950"></span>
                                    <span
                                        class="absolute left-1 top-1 h-3 w-3 rounded-full bg-amber-400 transition peer-checked:translate-x-4"></span>
                                </label>
                                <label class="flex items-center gap-2">
                                    <span>{{ __('Trocar por lanche') }}</span>
                                    <span class="relative inline-flex h-5 w-9 items-center">
                                        <input type="checkbox"
                                            class="peer sr-only focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0"
                                            wire:model="scheduleSettings.days.{{ $dateKey }}.meals.dinner.substitute_snack"
                                            wire:change="saveDaySettings('{{ $dateKey }}')"
                                            @if (!data_get($scheduleSettings, "days.$dateKey.meals.dinner.enabled")) disabled @endif />
                                        <span
                                            class="absolute inset-0 rounded-full bg-slate-200 transition-colors duration-200 peer-checked:bg-sky-950"></span>
                                        <span
                                            class="absolute left-1 top-1 h-3 w-3 rounded-full bg-amber-400 transition-transform duration-200 peer-checked:translate-x-4"></span>
                                    </span>
                                </label>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl border border-[color:var(--ee-app-border)] bg-white"
                    style="box-shadow: 0 0 2px 0 #052f4a">
                    <table class="w-full text-left text-sm">
                        <thead
                            class="text-xs bg-linear-to-b from-sky-200 to-sky-300 uppercase text-[color:var(--ee-app-muted)]">
                            <tr class="border-b border-[color:var(--ee-app-border)]">
                                <th class="px-3 py-2">{{ __('Horário') }}</th>
                                <th class="px-3 py-2">{{ __('Sessão') }}</th>
                                <th class="px-3 py-2">{{ __('Duração') }}</th>
                                <th class="px-3 py-2 text-right">{{ __('Ações') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[color:var(--ee-app-border)]">
                            @forelse ($items as $item)
                                @php
                                    $hasConflict = $item->status === 'CONFLICT';
                                    $tooltip = $hasConflict
                                        ? __('Conflito: sobreposição com item #:id', [
                                            'id' => $item->conflict_reason['with'] ?? '-',
                                        ])
                                        : '';
                                    $rowIndex = $loop->index;
                                    $hour = (int) $item->starts_at->format('H');
                                    if ($hour < 12) {
                                        $periodClass = 'bg-sky-100/30';
                                    } elseif ($hour < 18) {
                                        $periodClass = 'bg-amber-100/30';
                                    } else {
                                        $periodClass = 'bg-indigo-100/50';
                                    }
                                @endphp
                                <tr class="items-center {{ $periodClass }}"
                                    wire:key="schedule-item-{{ $item->id }}"
                                    :class="{
                                        'bg-red-50 text-red-700': {{ $hasConflict ? 'true' : 'false' }},
                                        'opacity-70': busy,
                                        'bg-yellow-100 ring-2 ring-yellow-300/70': draggingId === {{ $item->id }},
                                        'bg-yellow-50 ring-2 ring-yellow-300/80': dropTarget && dropTarget
                                            .date === '{{ $item->date->format('Y-m-d') }}' && dropTarget
                                            .startsAt === '{{ $item->starts_at->format('Y-m-d H:i:s') }}',
                                        'translate-y-2': draggingId && dropTarget && dropTarget.order !== null &&
                                            draggingIndex !== null && dropTarget.order < draggingIndex && dropTarget
                                            .order <= {{ $rowIndex }} && draggingIndex > {{ $rowIndex }},
                                        '-translate-y-2': draggingId && dropTarget && dropTarget.order !== null &&
                                            draggingIndex !== null && dropTarget.order > draggingIndex && dropTarget
                                            .order >= {{ $rowIndex }} && draggingIndex < {{ $rowIndex }},
                                        'transition-transform duration-150': draggingId,
                                    }"
                                    x-bind:draggable="draggingEnabledId === {{ $item->id }}"
                                    title="{{ $tooltip }}"
                                    @dragstart="startDrag({{ $item->id }}, '{{ $item->date->format('Y-m-d') }}', '{{ $item->starts_at->format('Y-m-d H:i:s') }}', {{ $rowIndex }})"
                                    @dragend="endDrag"
                                    @dragenter.stop.prevent="setDropTarget('{{ $item->date->format('Y-m-d') }}', '{{ $item->starts_at->format('Y-m-d H:i:s') }}', {{ $rowIndex }})"
                                    @dragover.stop.prevent="setDropTarget('{{ $item->date->format('Y-m-d') }}', '{{ $item->starts_at->format('Y-m-d H:i:s') }}', {{ $rowIndex }})"
                                    @drop.prevent="dropOn('{{ $item->date->format('Y-m-d') }}', '{{ $item->starts_at->format('Y-m-d H:i:s') }}')">
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <button type="button"
                                                class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-[color:var(--ee-app-border)] bg-white text-[color:var(--ee-app-muted)] transition hover:text-slate-700 {{ $item->is_locked ? 'cursor-not-allowed opacity-40' : 'cursor-grab' }}"
                                                title="{{ __('Arrastar para reordenar') }}"
                                                aria-label="{{ __('Arrastar para reordenar') }}"
                                                @if (!$item->is_locked) x-on:mousedown.stop="enableDrag({{ $item->id }})" @endif
                                                x-on:mouseup.window="disableDrag"
                                                @if (!$item->is_locked) x-on:touchstart.stop="enableDrag({{ $item->id }})" @endif
                                                x-on:touchend.window="disableDrag" x-on:mouseleave="disableDrag">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M8 6h.01M12 6h.01M16 6h.01M8 12h.01M12 12h.01M16 12h.01M8 18h.01M12 18h.01M16 18h.01" />
                                                </svg>
                                            </button>
                                            <span>{{ $item->starts_at->format('H:i') }} -
                                                {{ $item->ends_at->format('H:i') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2">
                                        <div class="font-semibold text-heading">{{ $item->title }}</div>
                                        @if ($item->section?->devotional)
                                            <div class="text-xs text-[color:var(--ee-app-muted)]">
                                                {{ $item->section->devotional }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        @if ($item->type === 'SECTION' && $item->suggested_duration_minutes)
                                            @php
                                                $minDuration = (int) ceil($item->suggested_duration_minutes * 0.8);
                                                $maxDuration = (int) floor($item->suggested_duration_minutes * 1.2);
                                            @endphp
                                            <div class="flex flex-wrap items-center gap-2">
                                                <input type="number" min="{{ $minDuration }}"
                                                    max="{{ $maxDuration }}"
                                                    value="{{ $item->planned_duration_minutes }}"
                                                    class="w-20 rounded-md border border-[color:var(--ee-app-border)] px-2 py-1 text-sm"
                                                    x-on:change="updateDuration({{ $item->id }}, '{{ $item->date->format('Y-m-d') }}', '{{ $item->starts_at->format('Y-m-d H:i:s') }}', $event.target.value)"
                                                    @if ($item->is_locked) disabled @endif />
                                                <span class="text-xs text-[color:var(--ee-app-muted)]">
                                                    {{ $minDuration }}-{{ $maxDuration }} {{ __('min') }}
                                                </span>
                                            </div>
                                        @else
                                            <div class="flex flex-wrap items-center gap-2">
                                                <input type="number" min="1" max="720"
                                                    value="{{ $item->planned_duration_minutes }}"
                                                    class="w-20 rounded-md border border-[color:var(--ee-app-border)] px-2 py-1 text-sm"
                                                    x-on:change="updateDuration({{ $item->id }}, '{{ $item->date->format('Y-m-d') }}', '{{ $item->starts_at->format('Y-m-d H:i:s') }}', $event.target.value)"
                                                    @if ($item->is_locked) disabled @endif />
                                                <span class="text-xs text-[color:var(--ee-app-muted)]">
                                                    {{ __('min') }}
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        <div class="flex flex-wrap justify-end gap-2">
                                            <flux:button type="button" size="sm" variant="danger"
                                                icon="trash" tooltip="{{ __('Remover sessão') }}"
                                                aria-label="{{ __('Remover sessão') }}"
                                                x-on:click.stop="deleteItem({{ $item->id }})">
                                            </flux:button>
                                            <flux:button type="button" size="sm"
                                                variant="{{ $item->is_locked ? 'outline' : 'primary' }}"
                                                class="whitespace-nowrap"
                                                icon="{{ $item->is_locked ? 'lock-open' : 'lock-closed' }}"
                                                tooltip="{{ $item->is_locked ? __('Destravar sessão') : __('Travar sessão') }}"
                                                aria-label="{{ $item->is_locked ? __('Destravar sessão') : __('Travar sessão') }}"
                                                x-on:click.stop="toggleLock({{ $item->id }}, {{ $item->is_locked ? 'false' : 'true' }})">
                                            </flux:button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="px-3 py-4 text-xs text-[color:var(--ee-app-muted)]" colspan="4">
                                        {{ __('Nenhum item para este dia.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div
                class="rounded-2xl border border-dashed border-[color:var(--ee-app-border)] p-6 text-sm text-[color:var(--ee-app-muted)]">
                {{ __('Nenhuma data cadastrada para este treinamento.') }}
            </div>
        @endforelse
    </section>

    <div x-show="modalOpen" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/50" x-on:click="$wire.closeModal()"></div>
        <div
            class="relative w-full max-w-lg rounded-2xl border border-[color:var(--ee-app-border)] bg-white p-6 shadow-xl">
            <div class="text-sm font-semibold text-heading">
                <span>{{ __('Adicionar sessão') }}</span>
            </div>

            <div class="mt-4 grid gap-4">
                <div class="grid gap-2">
                    <label class="text-xs font-semibold text-[color:var(--ee-app-muted)]"
                        for="schedule-title">{{ __('Título') }}</label>
                    <input id="schedule-title" type="text" wire:model="form.title"
                        class="w-full rounded-md border border-[color:var(--ee-app-border)] px-3 py-2 text-sm"
                        maxlength="255" />
                </div>
                <div class="grid gap-2">
                    <label class="text-xs font-semibold text-[color:var(--ee-app-muted)]"
                        for="schedule-type">{{ __('Tipo') }}</label>
                    <select id="schedule-type" wire:model="form.type"
                        class="w-full rounded-md border border-[color:var(--ee-app-border)] px-3 py-2 text-sm">
                        <option value="SECTION">{{ __('Sessão') }}</option>
                        <option value="BREAK">{{ __('Intervalo') }}</option>
                        <option value="DEVOTIONAL">{{ __('Devocional') }}</option>
                        <option value="MEAL">{{ __('Refeição') }}</option>
                        <option value="WELCOME">{{ __('Boas-vindas') }}</option>
                        <option value="OPENING">{{ __('Abertura') }}</option>
                        <option value="PRACTICE">{{ __('Prática') }}</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <label class="text-xs font-semibold text-[color:var(--ee-app-muted)]"
                            for="schedule-date">{{ __('Data') }}</label>
                        <input id="schedule-date" type="date" wire:model="form.date"
                            class="w-full rounded-md border border-[color:var(--ee-app-border)] px-3 py-2 text-sm" />
                    </div>
                    <div class="grid gap-2">
                        <label class="text-xs font-semibold text-[color:var(--ee-app-muted)]"
                            for="schedule-time">{{ __('Início') }}</label>
                        <input id="schedule-time" type="time" wire:model="form.time"
                            class="w-full rounded-md border border-[color:var(--ee-app-border)] px-3 py-2 text-sm" />
                    </div>
                </div>
                <div class="grid gap-2">
                    <label class="text-xs font-semibold text-[color:var(--ee-app-muted)]"
                        for="schedule-duration">{{ __('Duração (min)') }}</label>
                    <input id="schedule-duration" type="number" min="1" max="720"
                        wire:model="form.duration"
                        class="w-full rounded-md border border-[color:var(--ee-app-border)] px-3 py-2 text-sm" />
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <flux:button type="button" variant="outline" wire:click="closeModal">
                    {{ __('Cancelar') }}
                </flux:button>
                <flux:button type="button" variant="primary" wire:click="createItem" x-bind:disabled="busy">
                    {{ __('Salvar') }}
                </flux:button>
            </div>
        </div>
    </div>
</div>

@once
    <script data-navigate-once>
        window.trainingScheduleBoard = function(modalOpen) {
            return {
                modalOpen: modalOpen,
                draggingId: null,
                draggingEnabledId: null,
                draggingIndex: null,
                dropTarget: {
                    date: null,
                    startsAt: null,
                    order: null,
                },
                busy: false,
                enableDrag(id) {
                    this.draggingEnabledId = id;
                },
                disableDrag() {
                    this.draggingEnabledId = null;
                },
                setDropTarget(date, startsAt, order) {
                    if (!this.draggingId) {
                        return;
                    }

                    this.dropTarget = {
                        date,
                        startsAt,
                        order,
                    };
                },
                clearDropTarget() {
                    this.dropTarget = {
                        date: null,
                        startsAt: null,
                        order: null,
                    };
                },
                startDrag(id, date, startsAt, order) {
                    this.draggingId = id;
                    this.draggingIndex = order;
                    this.setDropTarget(date, startsAt, order);
                },
                endDrag() {
                    this.draggingId = null;
                    this.draggingIndex = null;
                    this.disableDrag();
                    this.clearDropTarget();
                },
                async regenerate() {
                    if (this.busy) {
                        return;
                    }

                    this.busy = true;
                    await this.$wire.regenerate();
                    this.busy = false;
                },
                async updateDuration(id, date, startsAt, duration) {
                    if (this.busy) {
                        return;
                    }

                    const parsed = Number(duration);

                    if (!Number.isFinite(parsed) || parsed <= 0) {
                        return;
                    }

                    this.busy = true;
                    await this.$wire.updateDuration(id, date, startsAt, parsed);
                    this.busy = false;
                },
                async dropOn(date, startsAt) {
                    if (!this.draggingId || this.busy) {
                        return;
                    }

                    this.busy = true;
                    await this.$wire.moveItem(this.draggingId, date, startsAt);
                    this.busy = false;
                    this.clearDropTarget();
                },
                async toggleLock(id, shouldLock) {
                    if (this.busy) {
                        return;
                    }

                    this.busy = true;
                    await this.$wire.toggleLock(id, shouldLock);
                    this.busy = false;
                },
                async deleteItem(id) {
                    if (this.busy) {
                        return;
                    }

                    if (!window.confirm('{{ __('Remover esta sessão?') }}')) {
                        return;
                    }

                    this.busy = true;
                    await this.$wire.deleteItem(id);
                    this.busy = false;
                },
            };
        };
    </script>
@endonce
