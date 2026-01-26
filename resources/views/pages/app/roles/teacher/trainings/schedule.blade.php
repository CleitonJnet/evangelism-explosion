<x-layouts.app :title="__('Programação do treinamento')">
    <x-src.toolbar.bar :title="__('Programação do treinamento')" :description="__('Organize horários e sessões do treinamento selecionado.')">
        <x-src.toolbar.button :href="route('app.teacher.training.index')" :label="__('Listar todos')" icon="list" :tooltip="__('Lista de treinamentos')" />
        <x-src.toolbar.button :href="route('app.teacher.training.show', $training)" :label="__('Detalhes')" icon="calendar" :tooltip="__('Detalhes do treinamento')" />
        <x-src.toolbar.button :href="route('app.teacher.training.schedule', $training)" :label="__('Programação')" icon="calendar" :active="true"
            :tooltip="__('Programação do evento')" />
        <x-src.toolbar.button :href="route('app.teacher.training.edit', $training)" :label="__('Editar')" icon="pencil" :tooltip="__('Editar treinamento')" />
    </x-src.toolbar.bar>

    @php
        $updateTemplate = route('app.teacher.trainings.schedule-items.update', [
            'training' => $training->id,
            'item' => 'ITEM_ID',
        ]);
        $lockTemplate = route('app.teacher.trainings.schedule-items.lock', [
            'training' => $training->id,
            'item' => 'ITEM_ID',
        ]);
        $unlockTemplate = route('app.teacher.trainings.schedule-items.unlock', [
            'training' => $training->id,
            'item' => 'ITEM_ID',
        ]);
    @endphp

    <div x-data="trainingScheduleBoard({
        regenerateUrl: @js(route('app.teacher.trainings.schedule.regenerate', $training)),
        updateUrlTemplate: @js($updateTemplate),
        lockUrlTemplate: @js($lockTemplate),
        unlockUrlTemplate: @js($unlockTemplate),
        csrf: @js(csrf_token()),
    })" class="space-y-6">
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
                    <select x-model="mode"
                        class="rounded-md border border-[color:var(--ee-app-border)] bg-white px-3 py-2 text-sm">
                        <option value="AUTO_ONLY">{{ __('Regenerar automático') }}</option>
                        <option value="FULL">{{ __('Regenerar tudo') }}</option>
                    </select>
                    <flux:button variant="primary" type="button" x-on:click="regenerate" x-bind:disabled="busy">
                        {{ __('Regenerar agenda') }}
                    </flux:button>
                </div>
            </div>
        </section>

        <section class="grid gap-6">
            @forelse ($eventDates as $eventDate)
                @php
                    $dateKey = $eventDate->date;
                    $items = $scheduleByDate->get($dateKey, collect())->sortBy('starts_at');
                    $dayStart = \Carbon\Carbon::parse($eventDate->date . ' ' . $eventDate->start_time)->format(
                        'Y-m-d H:i:s',
                    );
                @endphp
                <div class="rounded-2xl border border-[color:var(--ee-app-border)] bg-linear-to-br from-slate-100 via-white to-slate-200 p-4"
                    @dragover.prevent @drop.prevent="dropOn('{{ $dateKey }}', '{{ $dayStart }}')">
                    <div class="mb-4">
                        <div class="text-sm font-semibold text-heading">
                            {{ \Carbon\Carbon::parse($dateKey)->format('d/m/Y') }}</div>
                        <div class="text-xs text-[color:var(--ee-app-muted)]">
                            {{ $eventDate->start_time }} - {{ $eventDate->end_time }}
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-xl border border-[color:var(--ee-app-border)] bg-white">
                        <table class="w-full text-left text-sm">
                            <thead class="text-xs uppercase text-[color:var(--ee-app-muted)]">
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
                                    @endphp
                                    <tr class="items-center"
                                        :class="{
                                            'bg-red-50 text-red-700': {{ $hasConflict ? 'true' : 'false' }},
                                            'opacity-70': busy,
                                        }"
                                        x-bind:draggable="draggingEnabledId === {{ $item->id }}"
                                        title="{{ $tooltip }}"
                                        @dragstart="startDrag({{ $item->id }}, '{{ $item->date->format('Y-m-d') }}', '{{ $item->starts_at->format('Y-m-d H:i:s') }}')"
                                        @dragend="endDrag" @dragover.prevent
                                        @drop.prevent="dropOn('{{ $item->date->format('Y-m-d') }}', '{{ $item->starts_at->format('Y-m-d H:i:s') }}')">
                                        <td class="px-3 py-2 whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <button type="button"
                                                    class="cursor-grab inline-flex h-7 w-7 items-center justify-center rounded-md border border-[color:var(--ee-app-border)] bg-white text-[color:var(--ee-app-muted)] transition hover:text-slate-700"
                                                    title="{{ __('Arrastar para reordenar') }}"
                                                    aria-label="{{ __('Arrastar para reordenar') }}"
                                                    x-on:mousedown.stop="enableDrag({{ $item->id }})"
                                                    x-on:mouseup.window="disableDrag"
                                                    x-on:touchstart.stop="enableDrag({{ $item->id }})"
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
                                            {{ $item->planned_duration_minutes }} {{ __('min') }}
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            <flux:button type="button" size="sm"
                                                variant="{{ $item->is_locked ? 'outline' : 'primary' }}"
                                                class="whitespace-nowrap"
                                                x-on:click="toggleLock({{ $item->id }}, {{ $item->is_locked ? 'false' : 'true' }})">
                                                {{ $item->is_locked ? __('Destravar') : __('Travar') }}
                                            </flux:button>
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
    </div>

    @once
        <script>
            function trainingScheduleBoard(config) {
                return {
                    draggingId: null,
                    draggingEnabledId: null,
                    mode: 'AUTO_ONLY',
                    busy: false,
                    headers() {
                        return {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': config.csrf,
                        };
                    },
                    urlFromTemplate(template, id) {
                        return template.replace('ITEM_ID', id);
                    },
                    enableDrag(id) {
                        this.draggingEnabledId = id;
                    },
                    disableDrag() {
                        this.draggingEnabledId = null;
                    },
                    startDrag(id, date, startsAt) {
                        this.draggingId = id;
                    },
                    endDrag() {
                        this.draggingId = null;
                        this.disableDrag();
                    },
                    async regenerate() {
                        if (this.busy) {
                            return;
                        }

                        this.busy = true;

                        const response = await fetch(config.regenerateUrl, {
                            method: 'POST',
                            headers: this.headers(),
                            body: JSON.stringify({
                                mode: this.mode
                            }),
                        });

                        this.busy = false;

                        if (response.ok) {
                            window.location.reload();
                        }
                    },
                    async dropOn(date, startsAt) {
                        if (!this.draggingId || this.busy) {
                            return;
                        }

                        this.busy = true;

                        const response = await fetch(this.urlFromTemplate(config.updateUrlTemplate, this.draggingId), {
                            method: 'PATCH',
                            headers: this.headers(),
                            body: JSON.stringify({
                                date,
                                starts_at: startsAt
                            }),
                        });

                        this.busy = false;

                        if (response.ok) {
                            window.location.reload();
                        }
                    },
                    async toggleLock(id, shouldLock) {
                        if (this.busy) {
                            return;
                        }

                        this.busy = true;

                        const template = shouldLock ? config.lockUrlTemplate : config.unlockUrlTemplate;
                        const response = await fetch(this.urlFromTemplate(template, id), {
                            method: 'POST',
                            headers: this.headers(),
                        });

                        this.busy = false;

                        if (response.ok) {
                            window.location.reload();
                        }
                    },
                };
            }
        </script>
    @endonce
</x-layouts.app>
