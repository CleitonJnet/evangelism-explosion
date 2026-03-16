<x-layouts.app :title="__('Bases acompanhadas')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <x-app.portal.page-header
            eyebrow="Portal Staff"
            title="Bases acompanhadas"
            :description="'Leitura institucional das bases, com campo contextual para fieldworker e sem misturar acompanhamento com operacao local. Escopo atual: '.$basesIndex['scope'].'.'"
            :breadcrumbs="[
                ['label' => 'Portais', 'url' => route('app.start')],
                ['label' => 'Staff / Governanca', 'url' => route('app.portal.staff.dashboard')],
                ['label' => 'Bases acompanhadas', 'current' => true],
            ]" />

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <x-app.portal.stat-card label="Bases" :value="$basesIndex['counts']['bases']" hint="Bases dentro do escopo atual de acompanhamento." />
            <x-app.portal.stat-card label="Saudaveis" :value="$basesIndex['counts']['healthy']" hint="Bases sem pendencias institucionais agora." tone="emerald" />
            <x-app.portal.stat-card label="Pedem atencao" :value="$basesIndex['counts']['attention']" hint="Bases com follow-up ou faltas de evidencia." tone="amber" />
            <x-app.portal.stat-card label="Relatos pendentes" :value="$basesIndex['counts']['pending_reports']" hint="Fontes de campo ainda nao recebidas." tone="amber" />
            <x-app.portal.stat-card label="Follow-up" :value="$basesIndex['counts']['follow_up']" hint="Sinalizacoes institucionais abertas nas bases acompanhadas." tone="sky" />
        </section>

        <section class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-neutral-950">{{ __('Mapa de acompanhamento') }}</h2>
                    <p class="text-sm text-neutral-600">{{ __('Cada base aparece como unidade de leitura: saude geral, eventos, relatorios, pendencias e indicadores relevantes.') }}</p>
                </div>

                <span class="rounded-full bg-neutral-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-neutral-700">
                    {{ $basesIndex['scope'] }}
                </span>
            </div>

            <div class="grid gap-4 xl:grid-cols-2">
                @forelse ($basesIndex['items'] as $base)
                    @php
                        $healthBadgeClasses = match ($base['health']['tone']) {
                            'emerald' => 'bg-emerald-100 text-emerald-800',
                            'amber' => 'bg-amber-100 text-amber-800',
                            default => 'bg-sky-100 text-sky-800',
                        };
                    @endphp

                    <a href="{{ $base['detail_route'] }}" class="rounded-3xl border border-neutral-200 bg-neutral-50 p-5 transition hover:border-sky-300 hover:bg-sky-50">
                        <div class="flex flex-col gap-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h2 class="text-lg font-semibold text-neutral-950">{{ $base['name'] }}</h2>
                                    <p class="text-sm text-neutral-600">{{ $base['location'] }}</p>
                                    <p class="mt-1 text-xs text-neutral-500">{{ __('Ultimo recorte: :label', ['label' => $base['last_event_label']]) }}</p>
                                </div>

                                <span class="rounded-full px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] {{ $healthBadgeClasses }}">
                                    {{ $base['health']['label'] }}
                                </span>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                                <div class="rounded-2xl border border-white/70 bg-white/80 p-4">
                                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-neutral-500">{{ __('Eventos') }}</div>
                                    <div class="mt-2 text-2xl font-semibold text-neutral-950">{{ $base['events_total'] }}</div>
                                    <div class="mt-1 text-sm text-neutral-600">{{ __('Concluidos: :count', ['count' => $base['completed_events_count']]) }}</div>
                                </div>

                                <div class="rounded-2xl border border-white/70 bg-white/80 p-4">
                                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-neutral-500">{{ __('Relatorios') }}</div>
                                    <div class="mt-2 text-2xl font-semibold text-neutral-950">{{ $base['reports_received_count'] }}</div>
                                    <div class="mt-1 text-sm text-neutral-600">{{ __('Pendencias: :count', ['count' => $base['pending_reports_count']]) }}</div>
                                </div>

                                <div class="rounded-2xl border border-white/70 bg-white/80 p-4">
                                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-neutral-500">{{ __('Indicadores') }}</div>
                                    <div class="mt-2 text-2xl font-semibold text-neutral-950">{{ $base['follow_up_count'] }}</div>
                                    <div class="mt-1 text-sm text-neutral-600">{{ __('Follow-up ativo') }}</div>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2 text-xs">
                                @forelse ($base['fieldworkers'] as $fieldworker)
                                    <span class="rounded-full bg-sky-100 px-2.5 py-1 font-semibold text-sky-800">
                                        {{ $fieldworker }}
                                    </span>
                                @empty
                                    <span class="rounded-full bg-neutral-100 px-2.5 py-1 font-semibold text-neutral-700">
                                        {{ __('Sem fieldworker contextual vinculado') }}
                                    </span>
                                @endforelse
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 p-6 text-sm text-neutral-600 xl:col-span-2">
                        {{ __('Nenhuma base entrou no escopo atual de acompanhamento.') }}
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-layouts.app>
