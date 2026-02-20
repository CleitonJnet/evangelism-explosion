<div>
    <x-src.toolbar.header :title="__('Distribuição de Visitas STP')" :description="__('Distribua as visitas planejadas entre as equipes da sessão STP.')" />
    <x-src.toolbar.nav>
        <x-src.toolbar.button :href="route('app.teacher.trainings.show', $training)" :label="__('Detalhes do Evento')" icon="eye" :tooltip="__('Voltar para o Treinamento')" />
        <x-src.toolbar.button :href="route('app.teacher.trainings.statistics', $training)" :label="__('STP')" icon="users-chat" :tooltip="__('Saída de Treinamento Prático')" />
    </x-src.toolbar.nav>

    <div class="rounded-2xl bg-linear-to-br from-slate-100 via-white to-slate-200 p-4">
        <div class="mb-4 flex flex-wrap items-center gap-2">
            <label for="stp-board-session" class="text-xs font-semibold text-slate-700">Sessão STP:</label>
            <select
                id="stp-board-session"
                class="h-9 rounded-lg border border-slate-300 bg-white px-3 text-sm"
                wire:change="selectSession($event.target.value)"
            >
                <option value="">Selecione</option>
                @foreach ($sessions as $session)
                    <option value="{{ $session['id'] }}" @selected($activeSessionId === $session['id'])>
                        {{ $session['label'] }}
                    </option>
                @endforeach
            </select>

            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                wire:click="createPlannedApproach('visitor')"
            >
                + Visita
            </button>
            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                wire:click="createPlannedApproach('security_questionnaire')"
            >
                + Questionário
            </button>
            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                wire:click="createPlannedApproach('indication')"
            >
                + Indicação
            </button>
            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                wire:click="createPlannedApproach('lifestyle')"
            >
                + Estilo de Vida
            </button>
        </div>

        @if ($activeSessionId === null)
            <div class="rounded-xl border border-slate-200 bg-white px-4 py-8 text-center text-sm text-slate-500">
                Nenhuma sessão STP encontrada.
            </div>
        @else
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <section class="rounded-xl border border-slate-200 bg-white/80 p-3">
                    <div class="mb-3 text-sm font-bold text-slate-700">Fila</div>
                    <div class="js-stp-approach-list min-h-32 space-y-2 rounded-lg border border-dashed border-slate-300 bg-slate-50 p-2" data-container="queue">
                        @foreach ($queue as $approach)
                            <article
                                class="js-stp-approach-item cursor-grab rounded-lg border border-sky-300 bg-linear-to-br from-sky-100 via-white to-sky-200 p-2"
                                data-approach-id="{{ $approach['id'] }}"
                                wire:key="queue-approach-{{ $approach['id'] }}"
                                wire:click="openApproachModal({{ $approach['id'] }})"
                            >
                                <div class="text-xs font-semibold text-slate-700">{{ $approach['person_name'] }}</div>
                                <div class="text-[11px] uppercase tracking-wide text-slate-500">{{ $approach['type'] }}</div>
                                <div class="text-[11px] text-blue-700">{{ $approach['status'] }}</div>
                            </article>
                        @endforeach
                    </div>
                </section>

                @foreach ($teams as $team)
                    <section class="rounded-xl border border-slate-200 bg-white/80 p-3" wire:key="stp-team-column-{{ $team['id'] }}">
                        <div class="mb-3">
                            <div class="text-sm font-bold text-slate-700">{{ $team['name'] }}</div>
                            <div class="text-xs text-slate-500">Mentor: {{ $team['mentor']['name'] }}</div>
                            <div class="text-[11px] text-slate-500">Alunos: {{ $team['students_label'] }}</div>
                        </div>

                        <div class="js-stp-approach-list min-h-32 space-y-2 rounded-lg border border-dashed border-slate-300 bg-slate-50 p-2" data-container="team:{{ $team['id'] }}">
                            @foreach ($team['approaches'] as $approach)
                                <article
                                    class="js-stp-approach-item cursor-grab rounded-lg border border-emerald-300 bg-linear-to-br from-emerald-100 via-white to-emerald-200 p-2"
                                    data-approach-id="{{ $approach['id'] }}"
                                    wire:key="team-{{ $team['id'] }}-approach-{{ $approach['id'] }}"
                                    wire:click="openApproachModal({{ $approach['id'] }})"
                                >
                                    <div class="text-xs font-semibold text-slate-700">{{ $approach['person_name'] }}</div>
                                    <div class="text-[11px] uppercase tracking-wide text-slate-500">{{ $approach['type'] }}</div>
                                    <div class="text-[11px] text-blue-700">{{ $approach['status'] }}</div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        @endif
    </div>

    <flux:modal name="stp-approach-report" wire:model="showModal" class="max-w-6xl w-full">
        @if ($editingApproachId !== null && $editingApproach)
            <div class="space-y-4">
                <div>
                    <flux:heading size="lg">{{ __('Relatório da Visita') }}: {{ $editingApproachTypeLabel }}</flux:heading>
                    <flux:subheading>
                        {{ __('Registre os dados da abordagem e o resultado individual de cada ouvinte.') }}
                    </flux:subheading>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-xl border border-slate-300 bg-white/70 p-4 space-y-3">
                        <div class="text-sm font-semibold text-sky-950">Dados da abordagem</div>
                        <div class="grid gap-3 md:grid-cols-2">
                            <x-src.form.input name="form.person_name" wire:model.live="form.person_name" label="Nome" width_basic="1000" />
                            <x-src.form.input name="form.approach_date" wire:model.live="form.approach_date" label="Data da abordagem" width_basic="1000" type="date" />
                            <x-src.form.input name="form.phone" wire:model.live="form.phone" label="Telefone" width_basic="1000" type="tel" />
                            <x-src.form.input name="form.email" wire:model.live="form.email" label="Email" width_basic="1000" />
                            <x-src.form.input name="form.reference_point" wire:model.live="form.reference_point" label="Ponto de referência" width_basic="1000" />
                        </div>

                        <livewire:address-fields
                            wire:model="form.address"
                            title="Endereço da pessoa visitada"
                            :require-district-city-state="false"
                            wire:key="stp-address-fields-{{ $editingApproachId }}"
                        />
                    </div>

                    <div class="rounded-xl border border-slate-300 bg-white/70 p-4 space-y-3">
                        <div class="text-sm font-semibold text-sky-950">Resultado e acompanhamento</div>
                        <div class="grid gap-3 md:grid-cols-2">
                            <label class="grid gap-1 text-sm">
                                <span class="font-medium text-slate-700">Follow-up agendado</span>
                                <input type="datetime-local" class="h-10 rounded-lg border border-slate-300 bg-white px-3" wire:model.live="form.follow_up_scheduled_at">
                            </label>

                            <label class="inline-flex items-center gap-2 pt-6 text-sm font-medium text-slate-700">
                                <input type="checkbox" wire:model.live="form.means_growth" class="rounded border-slate-300">
                                Informou meios de crescimento
                            </label>

                            <div class="md:col-span-2">
                                <x-src.form.textarea name="form.payload.notes" wire:model.live="form.payload.notes" label="Observações da abordagem" width_basic="1000" />
                            </div>
                        </div>

                        @error('form.stp_team_id')
                            <p class="text-sm font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="rounded-xl border border-slate-300 bg-white/70 p-4 space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <div class="text-sm font-semibold text-sky-950">
                            Ouvintes presentes ({{ count(data_get($form, 'payload.listeners', [])) }})
                        </div>
                        <x-src.btn-silver type="button" wire:click="addListener">+ Adicionar ouvinte</x-src.btn-silver>
                    </div>

                    <div class="space-y-3">
                        @foreach (data_get($form, 'payload.listeners', []) as $index => $listener)
                            <div class="grid gap-3 rounded-lg border border-slate-200 bg-white p-3 md:grid-cols-3" wire:key="listener-row-{{ $index }}">
                                <x-src.form.input name="form.payload.listeners.{{ $index }}.name" wire:model.live="form.payload.listeners.{{ $index }}.name" label="Nome do ouvinte" width_basic="1000" />

                                <label class="grid gap-1 text-sm">
                                    <span class="font-medium text-slate-700">Resposta diagnóstica</span>
                                    <select class="h-10 rounded-lg border border-slate-300 bg-white px-3" wire:model.live="form.payload.listeners.{{ $index }}.diagnostic_answer">
                                        <option value="">Selecione</option>
                                        <option value="christ">Confia em Cristo</option>
                                        <option value="works">Confia em boas obras</option>
                                    </select>
                                </label>

                                <label class="grid gap-1 text-sm">
                                    <span class="font-medium text-slate-700">Resultado</span>
                                    <select class="h-10 rounded-lg border border-slate-300 bg-white px-3" wire:model.live="form.payload.listeners.{{ $index }}.result">
                                        <option value="">Selecione</option>
                                        <option value="decision">Decisão</option>
                                        <option value="no_decision_interested">Sem decisão / interessado</option>
                                        <option value="rejection">Rejeição</option>
                                        <option value="already_christian">Já cristão</option>
                                    </select>
                                </label>

                                <div class="md:col-span-3 flex justify-end">
                                    <x-src.btn-silver type="button" wire:click="removeListener({{ $index }})">Remover ouvinte</x-src.btn-silver>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <x-src.btn-silver type="button" wire:click="closeModal">{{ __('Close') }}</x-src.btn-silver>
                    <x-src.btn-silver type="button" wire:click="saveApproachDraft">Salvar</x-src.btn-silver>
                    <x-src.btn-gold type="button" wire:click="markAsDone">Concluir visita e fechar relatório</x-src.btn-gold>
                    @if ($canReview)
                        <x-src.btn-gold type="button" wire:click="markAsReviewed">Revisar</x-src.btn-gold>
                    @endif
                </div>
            </div>
        @endif
    </flux:modal>
</div>
