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
                    <flux:heading size="lg">{{ __('Relatório da Visita') }}</flux:heading>
                    <flux:subheading>
                        {{ __('Atualize dados da pessoa, resultado e informações públicas da abordagem.') }}
                    </flux:subheading>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-xl border border-slate-300 bg-white/70 p-4 space-y-3">
                        <div class="text-sm font-semibold text-sky-950">Dados da pessoa</div>
                        <div class="grid gap-3 md:grid-cols-2">
                            <x-src.form.input name="form.person_name" wire:model.live="form.person_name" label="Nome" width_basic="1000" />
                            <x-src.form.input name="form.phone" wire:model.live="form.phone" label="Telefone" width_basic="1000" />
                            <x-src.form.input name="form.email" wire:model.live="form.email" label="Email" width_basic="1000" />
                            <x-src.form.input name="form.reference_point" wire:model.live="form.reference_point" label="Ponto de referência" width_basic="1000" />
                            <x-src.form.input name="form.street" wire:model.live="form.street" label="Rua" width_basic="1000" />
                            <x-src.form.input name="form.number" wire:model.live="form.number" label="Número" width_basic="1000" />
                            <x-src.form.input name="form.complement" wire:model.live="form.complement" label="Complemento" width_basic="1000" />
                            <x-src.form.input name="form.district" wire:model.live="form.district" label="Bairro" width_basic="1000" />
                            <x-src.form.input name="form.city" wire:model.live="form.city" label="Cidade" width_basic="1000" />
                            <x-src.form.input name="form.state" wire:model.live="form.state" label="Estado" width_basic="1000" />
                            <x-src.form.input name="form.postal_code" wire:model.live="form.postal_code" label="CEP" width_basic="1000" />
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-300 bg-white/70 p-4 space-y-3">
                        <div class="text-sm font-semibold text-sky-950">Resultado e acompanhamento</div>
                        <div class="grid gap-3 md:grid-cols-2">
                            <x-src.form.input name="form.type" wire:model.live="form.type" label="Tipo" width_basic="1000" disabled />
                            <x-src.form.input name="form.status" wire:model.live="form.status" label="Status" width_basic="1000" disabled />
                            <x-src.form.input name="form.gospel_explained_times" wire:model.live="form.gospel_explained_times" label="Quantas vezes explicou" width_basic="1000" type="number" />
                            <x-src.form.input name="form.people_count" wire:model.live="form.people_count" label="Para quantas pessoas" width_basic="1000" type="number" />

                            <label class="grid gap-1 text-sm">
                                <span class="font-medium text-slate-700">Resultado</span>
                                <select class="h-10 rounded-lg border border-slate-300 bg-white px-3" wire:model.live="form.result">
                                    <option value="">Selecione</option>
                                    <option value="decision">Decisão</option>
                                    <option value="no_decision_interested">Sem decisão / interessado</option>
                                    <option value="rejection">Rejeição</option>
                                    <option value="already_christian">Já cristão</option>
                                </select>
                            </label>

                            <label class="grid gap-1 text-sm">
                                <span class="font-medium text-slate-700">Follow-up agendado</span>
                                <input type="datetime-local" class="h-10 rounded-lg border border-slate-300 bg-white px-3" wire:model.live="form.follow_up_scheduled_at">
                            </label>

                            <label class="inline-flex items-center gap-2 pt-6 text-sm font-medium text-slate-700">
                                <input type="checkbox" wire:model.live="form.means_growth" class="rounded border-slate-300">
                                Informou meios de crescimento
                            </label>
                        </div>

                        @error('form.stp_team_id')
                            <p class="text-sm font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="rounded-xl border border-slate-300 bg-white/70 p-4 space-y-3">
                    <div class="text-sm font-semibold text-sky-950">Relatório público</div>
                    <div class="grid gap-3 md:grid-cols-2">
                        <x-src.form.textarea name="form.public_q2_answer" wire:model.live="form.public_q2_answer" label="Resposta pública Q2" width_basic="1000" />
                        <x-src.form.textarea name="form.public_lesson" wire:model.live="form.public_lesson" label="Lição pública" width_basic="1000" />
                    </div>
                </div>

                @if (($form['type'] ?? null) === 'security_questionnaire')
                    <div class="rounded-xl border border-slate-300 bg-white/70 p-4 space-y-3">
                        <div class="text-sm font-semibold text-sky-950">Questionário de segurança</div>
                        <div class="grid gap-3 md:grid-cols-2">
                            <x-src.form.input name="form.payload.security_questionnaire.q1" wire:model.live="form.payload.security_questionnaire.q1" label="Q1" width_basic="1000" />
                            <x-src.form.input name="form.payload.security_questionnaire.q2" wire:model.live="form.payload.security_questionnaire.q2" label="Q2 (obrigatória para concluir)" width_basic="1000" />
                            <x-src.form.input name="form.payload.security_questionnaire.q3" wire:model.live="form.payload.security_questionnaire.q3" label="Q3" width_basic="1000" />
                            <x-src.form.input name="form.payload.security_questionnaire.q4" wire:model.live="form.payload.security_questionnaire.q4" label="Q4" width_basic="1000" />
                            <x-src.form.input name="form.payload.security_questionnaire.q5" wire:model.live="form.payload.security_questionnaire.q5" label="Q5" width_basic="1000" />
                        </div>
                        @error('form.payload.security_questionnaire.q2')
                            <p class="text-sm font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                @if (($form['type'] ?? null) === 'indication')
                    <div class="rounded-xl border border-slate-300 bg-white/70 p-4 space-y-3">
                        <div class="text-sm font-semibold text-sky-950">Indicação</div>
                        <div class="grid gap-3 md:grid-cols-2">
                            <x-src.form.input name="form.payload.indication.age" wire:model.live="form.payload.indication.age" label="Idade" width_basic="1000" />
                            <x-src.form.input name="form.payload.indication.profession" wire:model.live="form.payload.indication.profession" label="Profissão" width_basic="1000" />
                            <x-src.form.input name="form.payload.indication.religion" wire:model.live="form.payload.indication.religion" label="Religião" width_basic="1000" />
                            <x-src.form.textarea name="form.payload.indication.notes" wire:model.live="form.payload.indication.notes" label="Observações" width_basic="1000" />
                        </div>
                    </div>
                @endif

                @if (($form['type'] ?? null) === 'visitor')
                    <div class="rounded-xl border border-slate-300 bg-white/70 p-4 space-y-3">
                        <div class="text-sm font-semibold text-sky-950">Visitante</div>
                        <x-src.form.textarea name="form.payload.visitor.notes" wire:model.live="form.payload.visitor.notes" label="Observações" width_basic="1000" />
                    </div>
                @endif

                @if (($form['type'] ?? null) === 'lifestyle')
                    <div class="rounded-xl border border-slate-300 bg-white/70 p-4 space-y-3">
                        <div class="text-sm font-semibold text-sky-950">Estilo de Vida</div>
                        <x-src.form.textarea name="form.payload.lifestyle.notes" wire:model.live="form.payload.lifestyle.notes" label="Observações" width_basic="1000" />
                    </div>
                @endif

                <div class="flex justify-end gap-3">
                    <x-src.btn-silver type="button" wire:click="closeModal">{{ __('Close') }}</x-src.btn-silver>
                    <x-src.btn-silver type="button" wire:click="saveApproachDraft">Salvar rascunho</x-src.btn-silver>
                    <x-src.btn-gold type="button" wire:click="markAsDone">Concluir</x-src.btn-gold>
                    @if ($canReview)
                        <x-src.btn-gold type="button" wire:click="markAsReviewed">Revisar</x-src.btn-gold>
                    @endif
                </div>
            </div>
        @endif
    </flux:modal>
</div>
