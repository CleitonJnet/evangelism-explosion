<x-layouts.app :title="$base['church']['name']">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <x-app.portal.page-header
            eyebrow="Portal Staff"
            :title="$base['church']['name']"
            :description="'Leitura de acompanhamento da base pelo Staff, com escopo atual em '.$base['scope'].' e ponte contextual para fieldworker sem misturar governanca com operacao local.'"
            :breadcrumbs="[
                ['label' => 'Portais', 'url' => route('app.start')],
                ['label' => 'Staff / Governanca', 'url' => route('app.portal.staff.dashboard')],
                ['label' => 'Bases acompanhadas', 'url' => route('app.portal.staff.bases.index')],
                ['label' => $base['church']['name'], 'current' => true],
            ]">
            <flux:button variant="outline" :href="route('app.portal.staff.bases.index')" wire:navigate>
                {{ __('Voltar para bases') }}
            </flux:button>
        </x-app.portal.page-header>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-6">
            <x-app.portal.stat-card label="Saude geral" :value="$base['health']['label']" :hint="$base['health']['description']" :tone="$base['health']['tone']" />
            <x-app.portal.stat-card label="Eventos" :value="$base['counts']['events_total']" hint="Historico total mapeado para esta base." />
            <x-app.portal.stat-card label="Realizados" :value="$base['counts']['events_completed']" hint="Eventos concluidos neste recorte." tone="sky" />
            <x-app.portal.stat-card label="Relatorios recebidos" :value="$base['counts']['reports_received']" hint="Fontes ja recebidas do campo." tone="emerald" />
            <x-app.portal.stat-card label="Pendencias" :value="$base['counts']['pending_reports']" hint="Lacunas de evidencia ou revisao." tone="amber" />
            <x-app.portal.stat-card label="Follow-up" :value="$base['counts']['follow_up']" hint="Sinalizacoes institucionais abertas." />
        </section>

        <section class="grid gap-4 xl:grid-cols-[minmax(0,1.1fr)_minmax(20rem,0.9fr)]">
            <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-neutral-950">{{ __('Resumo da base') }}</h2>
                    <p class="text-sm text-neutral-600">{{ __('Dados institucionais reaproveitados da area de igreja/base, agora em modo de acompanhamento.') }}</p>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4">
                        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-neutral-500">{{ __('Localidade') }}</div>
                        <div class="mt-2 text-base font-semibold text-neutral-950">{{ trim(collect([$base['church']['city'], $base['church']['state']])->filter()->implode(' - ')) ?: __('Localidade nao informada') }}</div>
                        <div class="mt-2 text-sm text-neutral-600">{{ $base['church']['host_status'] }}</div>
                    </div>

                    <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4">
                        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-neutral-500">{{ __('Contato') }}</div>
                        <div class="mt-2 text-base font-semibold text-neutral-950">{{ $base['church']['contact'] ?: ($base['church']['pastor'] ?: __('Contato nao informado')) }}</div>
                        <div class="mt-2 text-sm text-neutral-600">{{ $base['church']['contact_phone'] ?: __('Telefone nao informado') }}</div>
                        <div class="text-sm text-neutral-600">{{ $base['church']['contact_email'] ?: __('Email nao informado') }}</div>
                    </div>
                </div>

                <div class="mt-6 rounded-3xl border border-sky-200 bg-sky-50 p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-base font-semibold text-sky-950">{{ $base['fieldworker_scope']['label'] }}</h3>
                            <p class="mt-1 text-sm text-sky-900">{{ $base['fieldworker_scope']['description'] }}</p>
                        </div>

                        <span class="rounded-full bg-white px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-sky-800">
                            {{ $base['scope'] }}
                        </span>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($base['fieldworker_scope']['permissions'] as $permission)
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-sky-900">
                                {{ $permission }}
                            </span>
                        @endforeach
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        @forelse ($base['church']['fieldworkers'] as $fieldworker)
                            <span class="rounded-full {{ $fieldworker['is_current_user'] ? 'bg-sky-900 text-white' : 'bg-white text-sky-900' }} px-3 py-1 text-xs font-semibold">
                                {{ $fieldworker['name'] }}{{ $fieldworker['is_current_user'] ? ' · voce' : '' }}
                            </span>
                        @empty
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-sky-900">
                                {{ __('Sem fieldworker contextual vinculado') }}
                            </span>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-neutral-950">{{ __('Pendencias e sinais') }}</h2>
                    <p class="text-sm text-neutral-600">{{ __('Fila curta do que pede leitura, cobranca de evidencia ou follow-up nesta base.') }}</p>
                </div>

                <div class="grid gap-3">
                    @forelse ($base['pending_items'] as $item)
                        <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold text-neutral-950">{{ $item['title'] }}</div>
                                    <div class="text-xs text-neutral-500">{{ $item['schedule_summary'] }}</div>
                                </div>

                                <span class="rounded-full px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] {{ $item['status']['tone'] === 'amber' ? 'bg-amber-100 text-amber-800' : 'bg-sky-100 text-sky-800' }}">
                                    {{ $item['status']['label'] }}
                                </span>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-2 text-xs">
                                @foreach ($item['sources'] as $source)
                                    <span class="rounded-full bg-white px-2.5 py-1 font-semibold text-neutral-700">
                                        {{ $source['label'] }}: {{ $source['status_label'] }}
                                    </span>
                                @endforeach
                            </div>

                            @if ($item['comparison_route'])
                                <a href="{{ $item['comparison_route'] }}" class="mt-3 inline-flex text-sm font-semibold text-sky-800">
                                    {{ __('Abrir leitura cruzada') }}
                                </a>
                            @endif
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 p-4 text-sm text-neutral-600">
                            {{ __('Nenhuma pendencia ativa nesta base no momento.') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="grid gap-4 xl:grid-cols-2">
            <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-neutral-950">{{ __('Eventos realizados') }}</h2>
                    <p class="text-sm text-neutral-600">{{ __('Historico recente de eventos concluidos pela base, com foco em leitura e acompanhamento.') }}</p>
                </div>

                <div class="grid gap-3">
                    @forelse ($base['completed_events'] as $item)
                        <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold text-neutral-950">{{ $item['title'] }}</div>
                                    <div class="text-xs text-neutral-500">{{ $item['teacher_name'] }} · {{ $item['schedule_summary'] }}</div>
                                </div>

                                <span class="rounded-full px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] {{ $item['status']['tone'] === 'emerald' ? 'bg-emerald-100 text-emerald-800' : ($item['status']['tone'] === 'amber' ? 'bg-amber-100 text-amber-800' : 'bg-sky-100 text-sky-800') }}">
                                    {{ $item['status']['label'] }}
                                </span>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-2 text-xs">
                                @foreach ($item['sources'] as $source)
                                    <span class="rounded-full bg-white px-2.5 py-1 font-semibold text-neutral-700">
                                        {{ $source['label'] }}: {{ $source['status_label'] }}
                                    </span>
                                @endforeach
                            </div>

                            @if ($item['latest_review_comment'])
                                <div class="mt-3 text-sm text-neutral-600">{{ $item['latest_review_comment'] }}</div>
                            @endif

                            @if ($item['comparison_route'])
                                <a href="{{ $item['comparison_route'] }}" class="mt-3 inline-flex text-sm font-semibold text-sky-800">
                                    {{ __('Abrir leitura cruzada') }}
                                </a>
                            @endif
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 p-4 text-sm text-neutral-600">
                            {{ __('Ainda nao ha eventos concluidos no recorte desta base.') }}
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-neutral-950">{{ __('Indicadores e agenda') }}</h2>
                    <p class="text-sm text-neutral-600">{{ __('Eventos futuros, indicadores relevantes e pontos que ajudam a conectar Staff e base.') }}</p>
                </div>

                <div class="grid gap-3">
                    @forelse ($base['upcoming_events'] as $item)
                        <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4">
                            <div class="text-sm font-semibold text-neutral-950">{{ $item['title'] }}</div>
                            <div class="text-xs text-neutral-500">{{ $item['teacher_name'] }} · {{ $item['schedule_summary'] }}</div>
                            <div class="mt-2 text-sm text-neutral-600">{{ __('Evento futuro acompanhado por esta base.') }}</div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 p-4 text-sm text-neutral-600">
                            {{ __('Nenhum evento futuro desta base entrou no recorte atual.') }}
                        </div>
                    @endforelse

                    <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4">
                        <div class="text-sm font-semibold text-neutral-950">{{ __('Indicadores relevantes') }}</div>
                        <div class="mt-3 grid gap-2 text-sm text-neutral-600">
                            <div>{{ __('Eventos futuros: :count', ['count' => $base['counts']['events_upcoming']]) }}</div>
                            <div>{{ __('Relatorios recebidos: :count', ['count' => $base['counts']['reports_received']]) }}</div>
                            <div>{{ __('Pendencias abertas: :count', ['count' => $base['counts']['pending_reports']]) }}</div>
                            <div>{{ __('Follow-up ativo: :count', ['count' => $base['counts']['follow_up']]) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-layouts.app>
