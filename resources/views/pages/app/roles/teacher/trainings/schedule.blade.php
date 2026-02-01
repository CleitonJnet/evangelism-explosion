<x-layouts.app :title="__('Programação do treinamento')">
    <x-src.toolbar.bar :title="__('Programação do treinamento')" :description="__('Organize horários e sessões do treinamento selecionado.')">
        <x-src.toolbar.button :href="route('app.teacher.trainings.index')" :label="__('Listar todos')" icon="list" :tooltip="__('Lista de treinamentos')" />
        <x-src.toolbar.button :href="route('app.teacher.trainings.show', $training)" :label="__('Detalhes')" icon="eye" :tooltip="__('Detalhes do treinamento')" />
        <x-src.toolbar.button :href="route('app.teacher.trainings.edit', $training)" :label="__('Editar')" icon="pencil" :tooltip="__('Editar treinamento')" />
        <x-src.toolbar.button :href="route('app.teacher.training.schedule', $training)" :label="__('Programação')" icon="calendar" :active="true"
            :tooltip="__('Programação do evento')" />
    </x-src.toolbar.bar>

    @php
        $updateTemplate = route('app.teacher.trainings.schedule-items.update', [
            'training' => $training->id,
            'item' => 'ITEM_ID',
        ]);
        $deleteTemplate = route('app.teacher.trainings.schedule-items.destroy', [
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
        $storeUrl = route('app.teacher.trainings.schedule-items.store', [
            'training' => $training->id,
        ]);
    @endphp

    <div x-data="trainingScheduleBoard({
        regenerateUrl: @js(route('app.teacher.trainings.schedule.regenerate', $training)),
        storeUrl: @js($storeUrl),
        updateUrlTemplate: @js($updateTemplate),
        deleteUrlTemplate: @js($deleteTemplate),
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
                    <flux:button variant="primary" type="button" icon="arrow-path"
                        tooltip="{{ __('Regenerar agenda') }}" aria-label="{{ __('Regenerar agenda') }}"
                        x-on:click="regenerate" x-bind:disabled="busy" />
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
                    @drop.prevent="dropOn('{{ $dateKey }}', '{{ $dayStart }}')">
                    <div class="mb-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <div class="text-sm font-semibold text-heading">
                                    {{ \Carbon\Carbon::parse($dateKey)->format('d/m/Y') }}</div>
                                <div class="text-xs text-[color:var(--ee-app-muted)]">
                                    {{ $eventDate->start_time }} - {{ $eventDate->end_time }}
                                </div>
                            </div>
                            <flux:button size="sm" type="button" variant="outline" icon="plus"
                                tooltip="{{ __('Adicionar sessão') }}" aria-label="{{ __('Adicionar sessão') }}"
                                x-on:click="openCreate('{{ $dateKey }}', '{{ substr($eventDate->start_time ?? '', 0, 5) }}')" />
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
                                    @endphp
                                    @php
                                        $rowIndex = $loop->index;
                                    @endphp
                                    @php
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
                                        :class="{
                                            'bg-red-50 text-red-700': {{ $hasConflict ? 'true' : 'false' }},
                                            'opacity-70': busy,
                                            'bg-yellow-100 ring-2 ring-yellow-300/70': draggingId ===
                                                {{ $item->id }},
                                            'bg-yellow-50 ring-2 ring-yellow-300/80': dropTarget && dropTarget
                                                .date === '{{ $item->date->format('Y-m-d') }}' && dropTarget
                                                .startsAt === '{{ $item->starts_at->format('Y-m-d H:i:s') }}',
                                            'translate-y-2': draggingId && dropTarget && dropTarget.order !== null &&
                                                draggingIndex !== null && dropTarget.order < draggingIndex &&
                                                dropTarget.order <= {{ $rowIndex }} && draggingIndex >
                                                {{ $rowIndex }},
                                            '-translate-y-2': draggingId && dropTarget && dropTarget.order !== null &&
                                                draggingIndex !== null && dropTarget.order > draggingIndex &&
                                                dropTarget.order >= {{ $rowIndex }} && draggingIndex <
                                                {{ $rowIndex }},
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
            <div class="absolute inset-0 bg-black/50" x-on:click="closeModal"></div>
            <div
                class="relative w-full max-w-lg rounded-2xl border border-[color:var(--ee-app-border)] bg-white p-6 shadow-xl">
                <div class="text-sm font-semibold text-heading">
                    <span>{{ __('Adicionar sessão') }}</span>
                </div>

                <div class="mt-4 grid gap-4">
                    <div class="grid gap-2">
                        <label class="text-xs font-semibold text-[color:var(--ee-app-muted)]"
                            for="schedule-title">{{ __('Título') }}</label>
                        <input id="schedule-title" type="text" x-model="form.title"
                            class="w-full rounded-md border border-[color:var(--ee-app-border)] px-3 py-2 text-sm"
                            maxlength="255" />
                    </div>
                    <div class="grid gap-2">
                        <label class="text-xs font-semibold text-[color:var(--ee-app-muted)]"
                            for="schedule-type">{{ __('Tipo') }}</label>
                        <select id="schedule-type" x-model="form.type"
                            class="w-full rounded-md border border-[color:var(--ee-app-border)] px-3 py-2 text-sm">
                            <option value="SECTION">{{ __('Sessão') }}</option>
                            <option value="BREAK">{{ __('Intervalo') }}</option>
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
                            <input id="schedule-date" type="date" x-model="form.date"
                                class="w-full rounded-md border border-[color:var(--ee-app-border)] px-3 py-2 text-sm" />
                        </div>
                        <div class="grid gap-2">
                            <label class="text-xs font-semibold text-[color:var(--ee-app-muted)]"
                                for="schedule-time">{{ __('Início') }}</label>
                            <input id="schedule-time" type="time" x-model="form.time"
                                class="w-full rounded-md border border-[color:var(--ee-app-border)] px-3 py-2 text-sm" />
                        </div>
                    </div>
                    <div class="grid gap-2">
                        <label class="text-xs font-semibold text-[color:var(--ee-app-muted)]"
                            for="schedule-duration">{{ __('Duração (min)') }}</label>
                        <input id="schedule-duration" type="number" min="1" max="720"
                            x-model="form.duration"
                            class="w-full rounded-md border border-[color:var(--ee-app-border)] px-3 py-2 text-sm" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <flux:button type="button" variant="outline" x-on:click="closeModal">
                        {{ __('Cancelar') }}
                    </flux:button>
                    <flux:button type="button" variant="primary" x-on:click="submitModal"
                        x-bind:disabled="busy">
                        {{ __('Salvar') }}
                    </flux:button>
                </div>
            </div>
        </div>
    </div>

    @once
        <script data-navigate-once>
            window.trainingScheduleBoard = function(config) {
                return {
                    draggingId: null,
                    draggingEnabledId: null,
                    draggingIndex: null,
                    dropTarget: {
                        date: null,
                        startsAt: null,
                        order: null,
                    },
                    mode: 'AUTO_ONLY',
                    busy: false,
                    modalOpen: false,
                    form: {
                        title: '',
                        type: 'SECTION',
                        date: '',
                        time: '',
                        duration: 60,
                    },
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
                    openCreate(date, time) {
                        this.form = {
                            title: '',
                            type: 'SECTION',
                            date: date || '',
                            time: time || '',
                            duration: 60,
                        };
                        this.modalOpen = true;
                    },
                    closeModal() {
                        this.modalOpen = false;
                    },
                    buildStartsAt(date, time) {
                        if (!date || !time) {
                            return null;
                        }

                        return `${date} ${time}:00`;
                    },
                    async submitModal() {
                        if (this.busy) {
                            return;
                        }

                        const startsAt = this.buildStartsAt(this.form.date, this.form.time);

                        if (!startsAt) {
                            return;
                        }

                        this.busy = true;

                        const payload = {
                            date: this.form.date,
                            starts_at: startsAt,
                            planned_duration_minutes: Number(this.form.duration),
                            title: this.form.title,
                            type: this.form.type,
                        };

                        const response = await fetch(config.storeUrl, {
                            method: 'POST',
                            headers: this.headers(),
                            body: JSON.stringify(payload),
                        });

                        this.busy = false;

                        if (response.ok) {
                            window.location.reload();
                        }
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
                        this.clearDropTarget();

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
                    async deleteItem(id) {
                        if (this.busy) {
                            return;
                        }

                        if (!window.confirm('{{ __('Remover esta sessão?') }}')) {
                            return;
                        }

                        this.busy = true;

                        const response = await fetch(this.urlFromTemplate(config.deleteUrlTemplate, id), {
                            method: 'DELETE',
                            headers: this.headers(),
                        });

                        this.busy = false;

                        if (response.ok) {
                            window.location.reload();
                        }
                    },
                };
            };
        </script>
    @endonce
</x-layouts.app>
