<div>
    <x-src.toolbar.header :title="__('Distribuição de Visitas STP')" :description="__('Distribua as visitas planejadas entre as equipes da sessão STP.')" />
    <x-src.toolbar.nav>
        <x-src.toolbar.button :href="route('app.teacher.trainings.show', $training)" :label="__('Detalhes do Evento')" icon="eye" :tooltip="__('Voltar para o Treinamento')" />
        <x-src.toolbar.button :href="route('app.teacher.trainings.statistics', $training)" :label="__('STP')" icon="users-chat" :tooltip="__('Saída de Treinamento Prático')" />
    </x-src.toolbar.nav>

    <div class="rounded-2xl bg-linear-to-br from-slate-100 via-white to-slate-200 p-4">
        <div class="mb-4 flex flex-wrap items-center gap-2">
            <label for="stp-board-session" class="text-xs font-semibold text-slate-700">Sessão STP:</label>
            <select id="stp-board-session" class="h-12 rounded-lg border border-slate-300 bg-white px-3 text-sm"
                wire:change="selectSession($event.target.value)">
                <option value="">Selecione</option>
                @foreach ($sessions as $session)
                    <option value="{{ $session['id'] }}" @selected($activeSessionId === $session['id'])>
                        {{ $session['label'] }}
                    </option>
                @endforeach
            </select>

            <x-src.btn-silver wire:click="createPlannedApproach('visitor')" :label="__('+ Visita')" />
            <x-src.btn-silver wire:click="createPlannedApproach('security_questionnaire')" :label="__('+ Questionário')" />
            <x-src.btn-silver wire:click="createPlannedApproach('indication')" :label="__('+ Indicação')" />
            <x-src.btn-silver wire:click="createPlannedApproach('lifestyle')" :label="__('+ Estilo de Vida')" />

        </div>

        @if ($activeSessionId === null)
            <div class="rounded-xl border border-slate-200 bg-white px-4 py-8 text-center text-sm text-slate-500">
                Nenhuma sessão STP encontrada.
            </div>
        @else
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <section class="rounded-xl border border-slate-200 bg-white/80 p-3">
                    <div class="text-sm font-bold text-slate-700">Fila de Abordagens</div>
                    <div class="mb-3 text-xs text-slate-500">Arraste as visitas para as equipes, para a devida
                        atribuição</div>
                    <div class="js-stp-approach-list min-h-32 space-y-2 rounded-lg border border-dashed border-slate-300 bg-slate-50 p-2"
                        data-container="queue">
                        @foreach ($queue as $approach)
                            <article
                                class="js-stp-approach-item cursor-grab rounded-lg border border-sky-300 bg-linear-to-br from-sky-100 via-white to-sky-200 p-2"
                                data-approach-id="{{ $approach['id'] }}" wire:key="queue-approach-{{ $approach['id'] }}"
                                wire:click="openApproachModal({{ $approach['id'] }})">
                                <div class="text-xs font-semibold text-sky-900">{{ $approach['type_label'] }}</div>
                                <div class="text-sm font-semibold tracking-wide text-slate-800">
                                    {{ $approach['person_name'] }}
                                </div>
                                <div class="text-[11px] text-blue-700">{{ $approach['status_label'] }}</div>
                            </article>
                        @endforeach
                    </div>
                </section>

                @foreach ($teams as $team)
                    <section class="rounded-xl border border-yellow-500 bg-yellow-100/20 p-3"
                        wire:key="stp-team-column-{{ $team['id'] }}">
                        <div class="mb-3">
                            <div class="text-sm font-bold text-slate-700">{{ $team['name'] }}</div>
                            <div class="text-xs text-orange-600">Mentor: <span
                                    class="font-bold">{{ $team['mentor']['name'] }}</span></div>
                            <div class="text-[11px] text-blue-600">Alunos: <span
                                    class="font-bold">{{ $team['students_label'] }}</span></div>
                        </div>

                        <div class="js-stp-approach-list min-h-32 space-y-2 rounded-lg border border-dashed border-slate-300 bg-slate-50 p-2"
                            data-container="team:{{ $team['id'] }}">
                            @foreach ($team['approaches'] as $approach)
                                <article
                                    class="js-stp-approach-item cursor-grab rounded-lg border border-emerald-300 bg-linear-to-br from-emerald-100 via-white to-emerald-200 p-2"
                                    data-approach-id="{{ $approach['id'] }}"
                                    wire:key="team-{{ $team['id'] }}-approach-{{ $approach['id'] }}"
                                    wire:click="openApproachModal({{ $approach['id'] }})">
                                    <div class="text-xs font-semibold text-emerald-700">{{ $approach['type_label'] }}
                                    </div>
                                    <div class="text-sm font-semibold tracking-wide text-slate-800">
                                        {{ $approach['person_name'] }}
                                    </div>
                                    <div class="text-[11px] text-blue-700">{{ $approach['status_label'] }}</div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        @endif
    </div>

    <flux:modal name="stp-approach-report" wire:model="showModal" class="max-w-6xl w-full bg-slate-100!">
        @if ($editingApproachId !== null && $editingApproach)
            <div class="relative space-y-4" x-data="{ showSavedMessage: false, savedMessage: '', hideTimer: null }"
                x-on:approach-draft-saved.window="
                    savedMessage = $event.detail.message ?? 'Alteração salva com sucesso.';
                    showSavedMessage = true;
                    clearTimeout(hideTimer);
                    hideTimer = setTimeout(() => showSavedMessage = false, $event.detail.duration ?? 3000);
                ">
                <div class="text-white">
                    <flux:heading size="lg">{{ __('Relatório da Visita') }}: {{ $editingApproachTypeLabel }}
                    </flux:heading>
                    <flux:subheading>
                        {{ __('Registre os dados da abordagem e o resultado individual de cada ouvinte.') }}
                    </flux:subheading>
                </div>

                <div class="flex flex-wrap gap-4">
                    <div class="rounded-xl border border-slate-300 bg-white p-4 space-y-6">
                        <div class="text-sm font-semibold text-sky-950">Dados da abordagem</div>
                        <div class="grid gap-5 md:grid-cols-2">
                            <x-src.form.input name="form.person_name" wire:model.live="form.person_name" label="Nome"
                                width_basic="1000" />
                            <x-src.form.input name="form.approach_date" wire:model.live="form.approach_date"
                                label="Data da abordagem" width_basic="1000" type="date" />
                            <x-src.form.input name="form.phone" wire:model.live="form.phone" label="Telefone"
                                width_basic="1000" type="tel" />
                            <x-src.form.input name="form.email" wire:model.live="form.email" label="Email"
                                width_basic="1000" />
                            <x-src.form.input name="form.reference_point" wire:model.live="form.reference_point"
                                label="Ponto de referência" width_basic="1000" />
                        </div>

                        <livewire:address-fields wire:model="form.address" title="Endereço da pessoa visitada"
                            :require-district-city-state="false" wire:key="stp-address-fields-{{ $editingApproachId }}" />
                    </div>
                </div>

                <div class="rounded-xl border border-slate-300 bg-white p-4 space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <div class="text-sm font-semibold text-sky-950">
                            Ouvintes presentes ({{ count(data_get($form, 'payload.listeners', [])) }})
                        </div>
                        <x-src.btn-silver type="button" wire:click="addListener">+ Adicionar
                            ouvinte</x-src.btn-silver>
                    </div>

                    <div class="">
                        @foreach (data_get($form, 'payload.listeners', []) as $index => $listener)
                            <div class="flex flex-wrap gap-3 hover:bg-slate-50 transition pb-2 pt-8"
                                wire:key="listener-row-{{ $index }}">

                                <x-src.form.input name="form.payload.listeners.{{ $index }}.name"
                                    wire:model.live="form.payload.listeners.{{ $index }}.name"
                                    label="Nome do ouvinte" width_basic="300" />

                                <x-src.form.select name="secound_question"
                                    wire:model.live="form.payload.listeners.{{ $index }}.diagnostic_answer"
                                    label="Resposta à 2ª Pergunta de diagnóstico" width_basic="300"
                                    :options="[
                                        ['value' => 'christ', 'label' => 'Confia em Cristo'],
                                        ['value' => 'works', 'label' => 'Confia em boas obras'],
                                    ]" />

                                <x-src.form.select name="secound_question"
                                    wire:model.live="form.payload.listeners.{{ $index }}.result"
                                    label="Resultado" width_basic="200" :options="[
                                        ['value' => 'decision', 'label' => 'Decisão'],
                                        ['value' => 'no_decision_interested', 'label' => 'Sem decisão / interessado'],
                                        ['value' => 'rejection', 'label' => 'Rejeição'],
                                        ['value' => 'already_christian', 'label' => 'Já cristão'],
                                    ]" />

                                <button type="button"
                                    class="flex items-center justify-center transition duration-200 hover:text-red-700 hover:bg-red-50 w-fit h-full px-2 py-2"
                                    title="{{ __('Remover') }}" wire:click="removeListener({{ $index }})"
                                    wire:loading.attr="disabled">
                                    <svg version="1.1" id="remove{{ $index }}"
                                        xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 fill-red-500"
                                        xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 459.739 459.739"
                                        xml:space="preserve">
                                        <path
                                            d="M229.869,0C102.917,0,0,102.917,0,229.869c0,126.952,102.917,229.869,229.869,229.869s229.869-102.917,229.869-229.869 C459.738,102.917,356.821,0,229.869,0z M313.676,260.518H146.063c-16.926,0-30.649-13.723-30.649-30.649 c0-16.927,13.723-30.65,30.649-30.65h167.613c16.925,0,30.649,13.723,30.649,30.65C344.325,246.795,330.601,260.518,313.676,260.518 z" />
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                    <div class="rounded-xl border border-slate-300 bg-white/70 p-4 space-y-3">
                        <div class="text-sm font-semibold text-sky-950">Resultado e acompanhamento</div>
                        <div class="grid gap-6 md:grid-cols-2">
                            <label class="grid gap-1 text-sm">
                                <span class="font-medium text-slate-700">Follow-up agendado</span>
                                <input type="datetime-local"
                                    class="h-10 rounded-lg border border-slate-300 bg-white px-3"
                                    wire:model.live="form.follow_up_scheduled_at">
                            </label>

                            <label class="inline-flex items-center gap-2 pt-6 text-sm font-medium text-slate-700">
                                <input type="checkbox" wire:model.live="form.means_growth"
                                    class="rounded border-slate-300">
                                Informou meios de crescimento
                            </label>

                            <div class="md:col-span-2">
                                <x-src.form.textarea name="form.payload.notes" wire:model.live="form.payload.notes"
                                    label="Observações da abordagem" width_basic="1000" />
                            </div>
                        </div>

                        @error('form.stp_team_id')
                            <p class="text-sm font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-between gap-3">
                    <div class="flex gap-2">
                        @if ($this->canDeleteApproach())
                            <button type="button"
                                class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 transition hover:bg-red-100"
                                x-on:click.prevent="if (window.confirm('{{ __('Deseja realmente remover esta visita?') }}')) { $wire.deleteApproach() }">
                                {{ __('Remover visita') }}
                            </button>
                        @endif
                    </div>
                    <div class="flex gap-2">
                        <x-src.btn-silver wire:click="closeModal" :label="__('Fechar')" />
                        @if ($this->canShowReviewButton())
                            <x-src.btn-silver wire:click="markAsReviewed" :label="__('Revisar')" />
                        @endif
                        <x-src.btn-silver wire:click="saveApproachDraft" :label="__('Salvar')" />
                        @if ($this->canMarkAsDone())
                            <x-src.btn-gold wire:click="markAsDone" :label="__('Concluir visita e fechar relatório')" />
                        @endif
                    </div>
                </div>

                <div class="pointer-events-none sticky bottom-0 z-10">
                    <div x-show="showSavedMessage" x-transition.opacity.duration.250ms
                        class="mx-auto w-fit rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-800 shadow-sm">
                        <span x-text="savedMessage"></span>
                    </div>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
