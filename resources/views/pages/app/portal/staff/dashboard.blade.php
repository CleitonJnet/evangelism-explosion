<x-layouts.app :title="__('Portal Staff / Governanca')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <x-app.portal.page-header
            eyebrow="Portal Staff"
            :title="$portalContext['headline']"
            :description="'Supervisao institucional das bases e dos eventos a partir de evidencias vindas do campo, sem transformar o Staff em area operacional.'"
            :breadcrumbs="[
                ['label' => 'Portais', 'url' => route('app.start')],
                ['label' => 'Staff / Governanca', 'current' => true],
            ]">
            <flux:button variant="primary" :href="route('app.portal.staff.bases.index')" wire:navigate>
                {{ __('Abrir bases acompanhadas') }}
            </flux:button>
        </x-app.portal.page-header>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <x-app.portal.stat-card label="Com evidencias" :value="$overview['counts']['with_reports']" hint="Eventos com pelo menos um relatorio recebido." tone="sky" />
            <x-app.portal.stat-card label="Pendentes de envio" :value="$overview['counts']['pending_submission']" hint="Fontes do campo ainda nao recebidas." tone="amber" />
            <x-app.portal.stat-card label="Aguardando leitura" :value="$overview['counts']['awaiting_review']" hint="Eventos prontos para leitura do Staff." />
            <x-app.portal.stat-card label="Follow-up" :value="$overview['counts']['follow_up']" hint="Casos com sinalizacao institucional ativa." tone="amber" />
            <x-app.portal.stat-card label="Governados" :value="$overview['counts']['governed']" hint="Eventos com leitura registrada pelo Staff." tone="emerald" />
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <x-app.portal.stat-card label="Bases acompanhadas" :value="$overview['bases']['counts']['bases']" hint="Bases em leitura institucional ou contextual." tone="sky" />
            <x-app.portal.stat-card label="Saudaveis" :value="$overview['bases']['counts']['healthy']" hint="Bases sem pendencias ativas neste recorte." tone="emerald" />
            <x-app.portal.stat-card label="Pedem atencao" :value="$overview['bases']['counts']['attention']" hint="Bases com follow-up ou lacunas de evidencias." tone="amber" />
            <x-app.portal.stat-card label="Relatos pendentes" :value="$overview['bases']['counts']['pending_reports']" hint="Fontes de campo ainda esperadas nas bases acompanhadas." tone="amber" />
            <x-app.portal.stat-card label="Follow-up nas bases" :value="$overview['bases']['counts']['follow_up']" hint="Sinalizacoes institucionais abertas por base." />
        </section>

        <section class="grid gap-4 xl:grid-cols-[minmax(0,1.15fr)_minmax(20rem,0.85fr)]">
            <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-neutral-950">{{ __('Fila de governanca') }}</h2>
                        <p class="text-sm text-neutral-600">{{ __('Leituras que pedem supervisao, cobranca de evidencias ou follow-up do Staff.') }}</p>
                    </div>

                    <a href="{{ route('app.portal.staff.reports.index') }}" class="text-sm font-semibold text-sky-800">{{ __('Ver fila completa') }}</a>
                </div>

                <div class="grid gap-3">
                    @forelse ($overview['pending_items'] as $item)
                        @php
                            $toneClasses = match ($item['tone']) {
                                'amber' => 'border-amber-200 bg-amber-50 text-amber-950',
                                'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-950',
                                default => 'border-sky-200 bg-sky-50 text-sky-950',
                            };
                        @endphp

                        <a href="{{ $item['comparison_route'] }}" class="rounded-2xl border p-4 transition hover:border-sky-300 hover:bg-sky-50 {{ $toneClasses }}">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold">{{ $item['title'] }}</div>
                                    <div class="text-sm opacity-80">{{ $item['church_name'] }} · {{ $item['teacher_name'] }}</div>
                                    <div class="mt-1 text-xs font-medium uppercase tracking-[0.18em] opacity-70">{{ $item['schedule_summary'] }}</div>
                                </div>

                                <span class="rounded-full bg-white/80 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]">
                                    {{ $item['status_label'] }}
                                </span>
                            </div>

                            @if ($item['pending_sources'] !== [])
                                <div class="mt-3 text-sm">
                                    {{ __('Pendentes: :sources', ['sources' => implode(', ', $item['pending_sources'])]) }}
                                </div>
                            @endif
                        </a>
                    @empty
                        <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 p-4 text-sm text-neutral-600">
                            {{ __('Nenhuma demanda de governanca aberta neste momento.') }}
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="flex flex-col gap-4">
                <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold text-neutral-950">{{ __('Bases acompanhadas') }}</h2>
                            <p class="text-sm text-neutral-600">{{ __('Visao por base para Staff e fieldworker acompanharem saude, pendencias e sinais do campo.') }}</p>
                        </div>

                        <a href="{{ route('app.portal.staff.bases.index') }}" class="text-sm font-semibold text-sky-800">{{ __('Abrir') }}</a>
                    </div>

                    <div class="grid gap-3">
                        @forelse ($overview['bases']['spotlight'] as $base)
                            <a href="{{ $base['detail_route'] }}" class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4 transition hover:border-sky-300 hover:bg-sky-50">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="text-sm font-semibold text-neutral-950">{{ $base['name'] }}</div>
                                        <div class="text-xs text-neutral-500">{{ $base['location'] }}</div>
                                    </div>

                                    <span class="rounded-full px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] {{ $base['health']['tone'] === 'emerald' ? 'bg-emerald-100 text-emerald-800' : ($base['health']['tone'] === 'amber' ? 'bg-amber-100 text-amber-800' : 'bg-sky-100 text-sky-800') }}">
                                        {{ $base['health']['label'] }}
                                    </span>
                                </div>

                                <div class="mt-3 text-sm text-neutral-600">
                                    {{ __('Eventos: :events · Pendencias: :pending · Follow-up: :followUp', ['events' => $base['events_total'], 'pending' => $base['pending_reports_count'], 'followUp' => $base['follow_up_count']]) }}
                                </div>
                            </a>
                        @empty
                            <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 p-4 text-sm text-neutral-600">
                                {{ __('Nenhuma base acompanhada entrou neste recorte ainda.') }}
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                    <div class="mb-4">
                        <h2 class="text-lg font-semibold text-neutral-950">{{ __('Atalhos de supervisao') }}</h2>
                        <p class="text-sm text-neutral-600">{{ __('Entradas desenhadas para leitura e governanca, nao para operacao diaria.') }}</p>
                    </div>

                    <div class="grid gap-3">
                        @foreach ($overview['shortcuts'] as $shortcut)
                            <a href="{{ $shortcut['route'] }}" class="rounded-2xl border border-neutral-200 bg-neutral-50 px-4 py-3 transition hover:border-sky-300 hover:bg-sky-50">
                                <div class="text-sm font-semibold text-neutral-950">{{ $shortcut['label'] }}</div>
                                <div class="text-xs text-neutral-500">{{ $shortcut['description'] }}</div>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                    <div class="mb-4">
                        <h2 class="text-lg font-semibold text-neutral-950">{{ __('Leitura recente') }}</h2>
                        <p class="text-sm text-neutral-600">{{ __('Ultimos eventos que entraram na fila de evidencias do Staff.') }}</p>
                    </div>

                    <div class="grid gap-3">
                        @forelse ($overview['recent_items'] as $item)
                            <a href="{{ $item['comparison_route'] }}" class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4 transition hover:border-sky-300 hover:bg-sky-50">
                                <div class="text-sm font-semibold text-neutral-950">{{ $item['title'] }}</div>
                                <div class="text-xs text-neutral-500">{{ $item['schedule_summary'] }}</div>
                                <div class="mt-2 text-sm text-neutral-600">{{ $item['status_label'] }}</div>
                            </a>
                        @empty
                            <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 p-4 text-sm text-neutral-600">
                                {{ __('Nenhum evento entrou recentemente na fila do Staff.') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-4 lg:grid-cols-2">
            @foreach ($menuSections as $section)
                <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-4">
                        <h2 class="text-lg font-semibold text-neutral-900">{{ $section['title'] }}</h2>

                        <div class="flex flex-col gap-3">
                            @foreach ($section['items'] as $item)
                                <a href="{{ route($item['route']) }}"
                                    class="flex items-center justify-between gap-4 rounded-2xl border border-neutral-200 px-4 py-3 transition hover:border-sky-300 hover:bg-sky-50">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-semibold text-neutral-900">{{ $item['label'] }}</span>
                                        @if ($item['description'] !== null)
                                            <span class="text-xs text-neutral-500">{{ $item['description'] }}</span>
                                        @endif
                                    </div>

                                    <span class="text-xs font-medium uppercase tracking-[0.18em] text-neutral-400">
                                        {{ $item['icon'] }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </section>
    </div>
</x-layouts.app>
