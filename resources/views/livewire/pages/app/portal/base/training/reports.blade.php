@php
    $churchCard = collect($reportSummary)->firstWhere('key', 'church') ?? [];
    $teacherCard = collect($reportSummary)->firstWhere('key', 'teacher') ?? [];
@endphp

<div class="space-y-6">
    <section class="grid gap-4 xl:grid-cols-2">
        @foreach ([$churchCard, $teacherCard] as $card)
            @php
                $toneClasses = match ($card['tone'] ?? 'slate') {
                    'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-900',
                    'amber' => 'border-amber-200 bg-amber-50 text-amber-900',
                    'sky' => 'border-sky-200 bg-sky-50 text-sky-900',
                    default => 'border-slate-200 bg-slate-50 text-slate-900',
                };
            @endphp

            <div class="rounded-3xl border p-6 shadow-sm {{ $toneClasses }}">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold">{{ $card['label'] ?? __('Relatorio') }}</h3>
                        <p class="mt-2 text-sm opacity-80">{{ $card['description'] ?? '' }}</p>
                    </div>
                    <span class="rounded-full bg-white/75 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em]">
                        {{ $card['status_label'] ?? __('Nao iniciado') }}
                    </span>
                </div>

                <div class="mt-4 grid gap-3 text-sm md:grid-cols-2">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.18em] opacity-70">{{ __('Fluxo') }}</div>
                        <div class="mt-1">{{ ($card['is_editable'] ?? false) ? __('Edicao liberada') : __('Edicao travada apos envio') }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.18em] opacity-70">{{ __('Ultimo envio') }}</div>
                        <div class="mt-1">{{ $card['submitted_at'] ?? __('Ainda nao enviado') }}</div>
                    </div>
                </div>

                @if (!empty($card['last_review_comment']))
                    <div class="mt-4 rounded-2xl border border-current/15 bg-white/70 px-4 py-3 text-sm">
                        <div class="font-semibold">{{ __('Observacao do Staff') }}</div>
                        <div class="mt-1 opacity-80">{{ $card['last_review_comment'] }}</div>
                    </div>
                @endif
            </div>
        @endforeach
    </section>

    <section class="grid gap-6 xl:grid-cols-2">
        <form wire:submit.prevent="saveChurchDraft" class="space-y-4 rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h3 class="text-lg font-semibold text-neutral-950">{{ __('Relatorio da igreja-base') }}</h3>
                    <p class="text-sm text-neutral-600">{{ __('Preencha o relato local do evento, salve em rascunho e envie quando a base concluir sua consolidacao.') }}</p>
                </div>
                <span class="rounded-full bg-neutral-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-neutral-700">
                    {{ $churchCard['status_label'] ?? __('Nao iniciado') }}
                </span>
            </div>

            @if ($churchFeedback)
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ $churchFeedback }}
                </div>
            @endif

            @error('churchReportLock')
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">{{ $message }}</div>
            @enderror

            @if (!($portalCapabilities['submitChurchEventReport'] ?? false))
                <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 px-4 py-3 text-sm text-neutral-600">
                    {{ __('Somente usuarios da igreja-base autorizados podem preencher e enviar este relatorio neste evento.') }}
                </div>
            @elseif (!($churchCard['is_editable'] ?? true))
                <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 px-4 py-3 text-sm text-neutral-600">
                    {{ __('A edicao foi travada apos o envio. O formulario sera reaberto apenas se o Staff solicitar revisao.') }}
                </div>
            @elseif ($churchCard['can_request_revision'] ?? false)
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    {{ __('O Staff solicitou revisao. Ajuste o rascunho e envie novamente quando concluir.') }}
                </div>
            @endif

            <div class="grid gap-4 md:grid-cols-2">
                <flux:input wire:model.blur="churchForm.title" :label="__('Titulo interno')" />
                <flux:input wire:model.blur="churchForm.attendance_registered" :label="__('Inscritos previstos')" type="number" min="0" />
                <flux:input wire:model.blur="churchForm.attendance_present" :label="__('Participantes presentes')" type="number" min="0" />
                <flux:input wire:model.blur="churchForm.attendance_decisions" :label="__('Decisoes registradas')" type="number" min="0" />
            </div>

            <flux:textarea wire:model.blur="churchForm.summary" :label="__('Resumo do relatorio')" rows="4" />
            @error('churchForm.summary')
                <div class="text-sm font-semibold text-red-600">{{ $message }}</div>
            @enderror

            <flux:textarea wire:model.blur="churchForm.local_highlights" :label="__('Destaques operacionais da base')" rows="4" />
            @error('churchForm.local_highlights')
                <div class="text-sm font-semibold text-red-600">{{ $message }}</div>
            @enderror

            <flux:textarea wire:model.blur="churchForm.follow_up_actions" :label="__('Acompanhamento e proximo contato')" rows="4" />
            <flux:textarea wire:model.blur="churchForm.support_needed" :label="__('Suporte ou pendencias apos o evento')" rows="4" />

            <div class="flex flex-wrap items-center gap-3">
                <flux:button variant="outline" type="submit" wire:loading.attr="disabled" :disabled="!($portalCapabilities['submitChurchEventReport'] ?? false) || !($churchCard['is_editable'] ?? true)">
                    {{ __('Salvar rascunho') }}
                </flux:button>
                <flux:button variant="primary" type="button" wire:click="submitChurchReport" wire:loading.attr="disabled" :disabled="!($portalCapabilities['submitChurchEventReport'] ?? false) || !($churchCard['is_editable'] ?? true)">
                    {{ __('Enviar relatorio') }}
                </flux:button>
            </div>
        </form>

        <form wire:submit.prevent="saveTeacherDraft" class="space-y-4 rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h3 class="text-lg font-semibold text-neutral-950">{{ __('Relatorio do professor') }}</h3>
                    <p class="text-sm text-neutral-600">{{ __('Consolide execucao, ministerio e recomendacoes do professor no mesmo contexto do evento.') }}</p>
                </div>
                <span class="rounded-full bg-neutral-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-neutral-700">
                    {{ $teacherCard['status_label'] ?? __('Nao iniciado') }}
                </span>
            </div>

            @if ($teacherFeedback)
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ $teacherFeedback }}
                </div>
            @endif

            @error('teacherReportLock')
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">{{ $message }}</div>
            @enderror

            @if (!($portalCapabilities['submitTeacherEventReport'] ?? false))
                <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 px-4 py-3 text-sm text-neutral-600">
                    {{ __('Somente o professor vinculado ao evento pode preencher e enviar este relatorio neste contexto.') }}
                </div>
            @elseif (!($teacherCard['is_editable'] ?? true))
                <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 px-4 py-3 text-sm text-neutral-600">
                    {{ __('A edicao foi travada apos o envio. O formulario sera reaberto apenas se o Staff solicitar revisao.') }}
                </div>
            @elseif ($teacherCard['can_request_revision'] ?? false)
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    {{ __('O Staff solicitou revisao. Ajuste o rascunho e envie novamente quando concluir.') }}
                </div>
            @endif

            <div class="grid gap-4 md:grid-cols-2">
                <flux:input wire:model.blur="teacherForm.title" :label="__('Titulo interno')" />
                <flux:input wire:model.blur="teacherForm.sessions_completed" :label="__('Sessoes concluidas')" type="number" min="0" />
                <flux:input wire:model.blur="teacherForm.people_trained" :label="__('Pessoas treinadas')" type="number" min="0" />
                <flux:input wire:model.blur="teacherForm.practical_contacts" :label="__('Contatos nas saidas praticas')" type="number" min="0" />
            </div>

            <flux:textarea wire:model.blur="teacherForm.summary" :label="__('Resumo do relatorio')" rows="4" />
            @error('teacherForm.summary')
                <div class="text-sm font-semibold text-red-600">{{ $message }}</div>
            @enderror

            <flux:textarea wire:model.blur="teacherForm.ministry_highlights" :label="__('Destaques ministeriais')" rows="4" />
            @error('teacherForm.ministry_highlights')
                <div class="text-sm font-semibold text-red-600">{{ $message }}</div>
            @enderror

            <flux:textarea wire:model.blur="teacherForm.recommendations" :label="__('Recomendacoes para a base e para o Staff')" rows="4" />
            <flux:textarea wire:model.blur="teacherForm.next_steps" :label="__('Proximos passos apos o evento')" rows="4" />

            <div class="flex flex-wrap items-center gap-3">
                <flux:button variant="outline" type="submit" wire:loading.attr="disabled" :disabled="!($portalCapabilities['submitTeacherEventReport'] ?? false) || !($teacherCard['is_editable'] ?? true)">
                    {{ __('Salvar rascunho') }}
                </flux:button>
                <flux:button variant="primary" type="button" wire:click="submitTeacherReport" wire:loading.attr="disabled" :disabled="!($portalCapabilities['submitTeacherEventReport'] ?? false) || !($teacherCard['is_editable'] ?? true)">
                    {{ __('Enviar relatorio') }}
                </flux:button>
            </div>
        </form>
    </section>
</div>
