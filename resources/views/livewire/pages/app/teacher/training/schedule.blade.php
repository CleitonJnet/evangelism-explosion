<div x-data x-on:schedule-alert.window="window.alert($event.detail.message)" class="space-y-6 relative"
    wire:loading.class="pointer-events-none"
    wire:target="regenerate,moveAfter,applyDuration,toggleDayBlock,addBreak,deleteBreak">
    <div class="absolute inset-0 opacity-0 pointer-events-all cursor-wait" wire:loading
        wire:target="regenerate,moveAfter,applyDuration,toggleDayBlock,addBreak,deleteBreak"></div>
    <x-src.toolbar.bar :title="__('Programação do treinamento')" :description="__('Organize horários e sessões do treinamento selecionado.')" justify="justify-between">
        <div class="flex flex-wrap gap-2">
            <x-src.toolbar.button :href="route('app.teacher.trainings.show', $training)" :label="__('Voltar')" icon="eye" :tooltip="__('Voltar para o Treinamento')" />
            <x-src.toolbar.button :href="route('app.teacher.trainings.edit', $training)" :label="__('Editar')" icon="pencil" :tooltip="__('Editar treinamento')" />
            <x-src.toolbar.button :href="route('app.teacher.trainings.schedule', $training)" :label="__('Programação')" icon="calendar" :active="true"
                :tooltip="__('Programação do evento')" />
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
    </x-src.toolbar.bar>

    @php
        $hasMultipleDays = $eventDates->count() > 1;
        $lastEventDate = $eventDates->last();
        $lastEventDateKey = $lastEventDate?->date;
        $lastEventEnd =
            $lastEventDate && $lastEventDate->end_time
                ? \Carbon\Carbon::parse($lastEventDate->date . ' ' . $lastEventDate->end_time)
                : null;
    @endphp
    <section class="grid gap-6">
        @forelse ($eventDates as $eventDate)
            @php
                $dateKey = $eventDate->date;
                $items = $scheduleByDate->get($dateKey, collect())->sortBy('position');
                $dayStart = \Carbon\Carbon::parse($eventDate->date . ' ' . $eventDate->start_time)->format(
                    'Y-m-d H:i:s',
                );
                $dayUiFlags = $dayUi[$dateKey] ?? [];
                $showBreakfast = (bool) ($dayUiFlags['showBreakfast'] ?? false);
                $showLunch = (bool) ($dayUiFlags['showLunch'] ?? false);
                $showSnack = (bool) ($dayUiFlags['showSnack'] ?? false);
                $showDinner = (bool) ($dayUiFlags['showDinner'] ?? false);
            @endphp
            <div class="rounded-2xl border border-[color:var(--ee-app-border)] bg-linear-to-br from-slate-100 via-white to-slate-200 p-4"
                wire:key="schedule-day-{{ $dateKey }}">
                <div class="">
                    <div class="flex flex-wrap items-start justify-between gap-2 pb-2">
                        <div class="flex flex-wrap items-center bg-sky-950/10 rounded">
                            <div
                                class="flex flex-wrap items-center gap-2 rounded bg-sky-950 px-2 pt-1 pb-0.5 text-[11px] font-semibold uppercase tracking-wide text-amber-200">
                                @if ($hasMultipleDays)
                                    <span class="">
                                        {{ __('Dia') }} {{ $loop->iteration }}:
                                    </span>
                                @endif
                                <div class="font-semibold text-heading text-slate-50">
                                    {{ \Illuminate\Support\Str::ucfirst(\Carbon\Carbon::parse($dateKey)->locale('pt_BR')->isoFormat('dddd')) }}
                                    -
                                    {{ \Carbon\Carbon::parse($dateKey)->format('d/m') }}
                                </div>
                            </div>
                            <div class="text-xs text-amber-700 px-2">
                                {{ $eventDate->start_time }} - {{ $eventDate->end_time }}
                            </div>
                        </div>
                        <div
                            class="flex flex-auto flex-wrap items-center justify-end gap-2 text-xs text-[color:var(--ee-app-muted)]">
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

                <div class="overflow-hidden rounded-xl border border-[color:var(--ee-app-border)] bg-white"
                    style="box-shadow: 0 0 2px 0 #052f4a">
                    <table class="w-full text-left text-sm">
                        <thead
                            class="text-xs bg-linear-to-b from-sky-200 to-sky-300 uppercase text-[color:var(--ee-app-muted)]">
                            <tr class="border-b border-[color:var(--ee-app-border)]">
                                <th class="px-3 py-2 w-36">{{ __('Horário') }}</th>
                                <th class="px-3 py-2">{{ __('Sessão') }}</th>
                                <th class="px-3 py-2 w-36">{{ __('Duração') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[color:var(--ee-app-border)] js-schedule-day-list"
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
                                                class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-[color:var(--ee-app-border)] bg-white text-[color:var(--ee-app-muted)] transition hover:text-slate-700 hover:bg-sky-500 cursor-grab js-drag-handle"
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
                                            <div class="text-xs text-[color:var(--ee-app-muted)]">
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
                                                    class="w-12 rounded-md border border-[color:var(--ee-app-border)] text-right py-1 text-sm bg-white/60 focus-within:bg-white"
                                                    wire:model.blur="durationInputs.{{ $item->id }}"
                                                    wire:blur="applyDuration({{ $item->id }})"
                                                    wire:loading.attr="disabled" wire:target="applyDuration" />
                                                <div
                                                    class="text-[10px] text-[color:var(--ee-app-muted)] flex flex-col">
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
                                                    class="w-12 rounded-md border border-[color:var(--ee-app-border)] text-center md:text-right py-1 text-sm bg-white/60 focus-within:bg-white"
                                                    wire:model.blur="durationInputs.{{ $item->id }}"
                                                    wire:blur="applyDuration({{ $item->id }})"
                                                    wire:loading.attr="disabled" wire:target="applyDuration" />
                                                <span class="text-xs text-[color:var(--ee-app-muted)]">
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
                                    <td class="px-3 py-4 text-xs text-[color:var(--ee-app-muted)]" colspan="3">
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

</div>
