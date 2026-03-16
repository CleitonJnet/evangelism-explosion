<div class="space-y-6">
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
        <x-app.portal.stat-card label="Status" :value="$comparison['queue_item']['status_label']" hint="Leitura atual da governanca para este evento." :tone="$comparison['queue_item']['tone']" />
        <x-app.portal.stat-card label="Fontes recebidas" :value="$comparison['queue_item']['received_reports_count'].'/2'" hint="Igreja-base e professor." />
        <x-app.portal.stat-card label="Fontes revisadas" :value="$comparison['queue_item']['reviewed_reports_count'].'/2'" hint="Relatorios ja concluidos pelo Staff." tone="emerald" />
        <x-app.portal.stat-card label="Classificacao" :value="$comparison['queue_item']['classification'] ?? 'Nao classificado'" hint="Leitura institucional mais recente." />
        <x-app.portal.stat-card label="Follow-up" :value="$comparison['queue_item']['follow_up_required'] ? 'Sim' : 'Nao'" hint="Sinalizacao institucional vigente." :tone="$comparison['queue_item']['follow_up_required'] ? 'amber' : 'neutral'" />
    </section>

    <section class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
        <div class="mb-4 flex flex-col gap-2">
            <h2 class="text-lg font-semibold text-neutral-950">{{ __('Leitura de governanca') }}</h2>
            <p class="text-sm text-neutral-600">{{ __('Resumo supervisionado do evento, sempre a partir das evidencias vindas do campo.') }}</p>
        </div>

        <div class="grid gap-3">
            @foreach ($comparison['findings'] as $finding)
                <div class="rounded-2xl border border-neutral-200 bg-neutral-50 px-4 py-3 text-sm text-neutral-700">
                    {{ $finding }}
                </div>
            @endforeach
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-2">
        @foreach ($comparison['sources'] as $source)
            @php
                $toneClasses = match ($source['tone']) {
                    'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-950',
                    'amber' => 'border-amber-200 bg-amber-50 text-amber-950',
                    'sky' => 'border-sky-200 bg-sky-50 text-sky-950',
                    default => 'border-neutral-200 bg-neutral-50 text-neutral-950',
                };
            @endphp

            <article class="rounded-3xl border p-6 shadow-sm {{ $toneClasses }}">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold">{{ $source['label'] }}</h2>
                        <p class="mt-1 text-sm opacity-80">{{ $source['description'] }}</p>
                    </div>

                    <span class="rounded-full bg-white/80 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em]">
                        {{ $source['status_label'] }}
                    </span>
                </div>

                @if (!$source['exists'])
                    <div class="mt-4 rounded-2xl border border-current/15 bg-white/70 px-4 py-3 text-sm">
                        {{ __('Nenhum relatorio recebido desta fonte ate o momento.') }}
                    </div>
                @else
                    <div class="mt-4 grid gap-3 md:grid-cols-3">
                        @foreach ($source['metrics'] as $metric)
                            <div class="rounded-2xl border border-current/15 bg-white/70 p-4">
                                <div class="text-xs font-semibold uppercase tracking-[0.18em] opacity-70">{{ $metric['label'] }}</div>
                                <div class="mt-2 text-2xl font-semibold">{{ $metric['value'] ?? '—' }}</div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 rounded-2xl border border-current/15 bg-white/70 px-4 py-4 text-sm">
                        <div class="text-xs font-semibold uppercase tracking-[0.18em] opacity-70">{{ __('Resumo') }}</div>
                        <div class="mt-2 whitespace-pre-line">{{ $source['summary'] ?? __('Resumo nao informado.') }}</div>
                    </div>

                    <div class="mt-4 grid gap-3">
                        @foreach ($source['notes'] as $note)
                            <div class="rounded-2xl border border-current/15 bg-white/70 px-4 py-4 text-sm">
                                <div class="text-xs font-semibold uppercase tracking-[0.18em] opacity-70">{{ $note['label'] }}</div>
                                <div class="mt-2 whitespace-pre-line">{{ $note['value'] ?? __('Nao informado.') }}</div>
                            </div>
                        @endforeach
                    </div>

                    @if ($source['latest_review'])
                        <div class="mt-4 rounded-2xl border border-current/15 bg-white/80 px-4 py-4 text-sm">
                            <div class="font-semibold">{{ __('Ultima leitura do Staff') }}</div>
                            <div class="mt-1">{{ $source['latest_review']['outcome_label'] }} · {{ $source['latest_review']['reviewer_name'] }} · {{ $source['latest_review']['reviewed_at'] }}</div>
                            @if ($source['latest_review']['classification'])
                                <div class="mt-1">{{ __('Classificacao: :classification', ['classification' => $source['latest_review']['classification']]) }}</div>
                            @endif
                            @if ($source['latest_review']['follow_up_required'])
                                <div class="mt-1 font-semibold text-amber-900">{{ __('Follow-up institucional sinalizado.') }}</div>
                            @endif
                            @if ($source['latest_review']['comment'])
                                <div class="mt-2 whitespace-pre-line">{{ $source['latest_review']['comment'] }}</div>
                            @endif
                        </div>
                    @endif
                @endif
            </article>
        @endforeach
    </section>

    <section class="grid gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(22rem,0.9fr)]">
        <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-neutral-950">{{ __('Historico de leituras') }}</h2>
                <p class="text-sm text-neutral-600">{{ __('Rastro de supervisao preservado por fonte, com comentario, classificacao e follow-up.') }}</p>
            </div>

            <div class="grid gap-4 xl:grid-cols-2">
                @foreach ($comparison['sources'] as $source)
                    <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4">
                        <div class="mb-3 text-sm font-semibold text-neutral-950">{{ $source['label'] }}</div>

                        <div class="grid gap-3">
                            @forelse ($source['history'] as $entry)
                                <div class="rounded-2xl border border-neutral-200 bg-white px-4 py-3 text-sm text-neutral-700">
                                    <div class="font-semibold text-neutral-950">{{ $entry['outcome_label'] }}</div>
                                    <div class="text-xs text-neutral-500">{{ $entry['reviewer_name'] }} · {{ $entry['reviewed_at'] }}</div>
                                    @if ($entry['classification'])
                                        <div class="mt-1 text-xs font-semibold uppercase tracking-[0.18em] text-neutral-500">{{ $entry['classification'] }}</div>
                                    @endif
                                    @if ($entry['follow_up_required'])
                                        <div class="mt-1 text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">{{ __('Follow-up') }}</div>
                                    @endif
                                    @if ($entry['comment'])
                                        <div class="mt-2 whitespace-pre-line">{{ $entry['comment'] }}</div>
                                    @endif
                                </div>
                            @empty
                                <div class="rounded-2xl border border-dashed border-neutral-300 bg-white px-4 py-3 text-sm text-neutral-500">
                                    {{ __('Nenhuma leitura registrada para esta fonte.') }}
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        @if ($canManageReviews)
            <form wire:submit="saveReview" class="space-y-4 rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
            <div>
                <h2 class="text-lg font-semibold text-neutral-950">{{ __('Registrar leitura do Staff') }}</h2>
                <p class="text-sm text-neutral-600">{{ __('Comentario institucional, classificacao e follow-up, sem operar o evento no dia a dia.') }}</p>
            </div>

            @if ($feedback)
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ $feedback }}
                </div>
            @endif

            <flux:select wire:model="reviewForm.action" :label="__('Acao da governanca')">
                <option value="commented">{{ __('Registrar comentario') }}</option>
                <option value="approved">{{ __('Concluir leitura') }}</option>
                <option value="changes_requested">{{ __('Solicitar ajustes ao campo') }}</option>
            </flux:select>
            @error('reviewForm.action')
                <div class="text-sm font-semibold text-red-600">{{ $message }}</div>
            @enderror

            <flux:select wire:model="reviewForm.classification" :label="__('Classificacao')">
                <option value="aligned">{{ __('Alinhado') }}</option>
                <option value="attention">{{ __('Atencao') }}</option>
                <option value="critical">{{ __('Critico') }}</option>
            </flux:select>

            <flux:textarea wire:model.blur="reviewForm.comment" :label="__('Comentario do Staff')" rows="6" />
            @error('reviewForm.comment')
                <div class="text-sm font-semibold text-red-600">{{ $message }}</div>
            @enderror

            <div class="rounded-2xl border border-neutral-200 bg-neutral-50 px-4 py-3">
                <flux:switch wire:model="reviewForm.follow_up_required" :label="__('Sinalizar follow-up institucional')" />
            </div>

            <flux:button variant="primary" type="submit" wire:loading.attr="disabled">
                {{ __('Registrar leitura') }}
            </flux:button>
            </form>
        @else
            <section class="space-y-4 rounded-3xl border border-sky-200 bg-sky-50 p-6 shadow-sm">
                <div>
                    <h2 class="text-lg font-semibold text-sky-950">{{ __('Leitura contextual do fieldworker') }}</h2>
                    <p class="text-sm text-sky-900">{{ __('Voce pode acompanhar evidencias, historico e sinais desta base, mas a leitura institucional e os retornos oficiais ao campo ficam com Board e Director.') }}</p>
                </div>

                <div class="rounded-2xl border border-sky-200 bg-white/80 px-4 py-3 text-sm text-sky-900">
                    {{ __('Use esta comparacao para conectar Staff e base, preservando a separacao entre governanca e operacao local.') }}
                </div>
            </section>
        @endif
    </section>
</div>
