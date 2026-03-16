<x-layouts.app :title="__('Portal Base e Treinamentos')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <x-app.portal.page-header
            eyebrow="Portal Base"
            :title="$portalContext['headline']"
            :description="'Operacao local e ministerial organizada por contexto: base, treinamentos em que voce serve e eventos sediados pela sua igreja-base.'"
            :breadcrumbs="[
                ['label' => 'Portais', 'url' => route('app.start')],
                ['label' => 'Base e Treinamentos', 'current' => true],
            ]">
            @if (($navigation['primaryAreaRoute'] ?? null) && ($navigation['primaryAreaLabel'] ?? null) !== 'Visao geral')
                <flux:button variant="primary" :href="$navigation['primaryAreaRoute']" wire:navigate>
                    {{ __('Abrir :area', ['area' => $navigation['primaryAreaLabel']]) }}
                </flux:button>
            @endif
        </x-app.portal.page-header>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-6">
            <x-app.portal.stat-card label="Sirvo em breve" :value="$overview['counts']['serving_upcoming']" hint="Treinamentos com atuacao proxima." tone="sky" />
            <x-app.portal.stat-card label="Em andamento" :value="$overview['counts']['in_progress_serving']" hint="Frentes ativas agora." tone="emerald" />
            <x-app.portal.stat-card label="Eventos da base" :value="$overview['counts']['hosted_events']" hint="Agenda sediada pela sua base." />
            <x-app.portal.stat-card label="Programacao pendente" :value="$overview['counts']['pending_programming']" hint="Eventos com agenda incompleta." tone="amber" />
            <x-app.portal.stat-card label="Relatorios pendentes" :value="$overview['counts']['pending_reports']" hint="Eventos concluidos sem relato." tone="amber" />
            <x-app.portal.stat-card label="Alertas de acervo" :value="$overview['counts']['inventory_alerts']" hint="Estoques com baixo saldo ou inativos." />
        </section>

        <section class="grid gap-4 xl:grid-cols-3">
            @if ($navigation['canViewMyBase'])
                <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold text-neutral-950">{{ __('Minha Base') }}</h2>
                            <p class="text-sm text-neutral-600">{{ __('Igreja-base, base anfitria e acervo sob o olhar local.') }}</p>
                        </div>

                        <a href="{{ route('app.portal.base.my-base') }}" class="text-sm font-semibold text-sky-800">{{ __('Abrir') }}</a>
                    </div>

                    <div class="grid gap-3">
                        @if ($overview['my_base']['church'])
                            <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4">
                                <div class="text-sm font-semibold text-neutral-950">{{ $overview['my_base']['church']['name'] }}</div>
                                <div class="text-sm text-neutral-600">{{ $overview['my_base']['church']['city'] ?: 'Cidade nao informada' }}{{ $overview['my_base']['church']['state'] ? ' - '.$overview['my_base']['church']['state'] : '' }}</div>
                                <div class="mt-2 text-xs font-medium uppercase tracking-[0.18em] text-neutral-500">{{ $overview['my_base']['church']['host_label'] }}</div>
                            </div>
                        @else
                            <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 p-4 text-sm text-neutral-600">
                                {{ __('Nenhuma igreja-base vinculada ao seu perfil neste momento.') }}
                            </div>
                        @endif

                        @forelse ($overview['my_base']['inventory_alerts'] as $inventory)
                            <a href="{{ $inventory['route'] }}" class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4 transition hover:border-sky-300 hover:bg-sky-50">
                                <div class="text-sm font-semibold text-neutral-950">{{ $inventory['name'] }}</div>
                                <div class="text-xs text-neutral-500">{{ $inventory['responsible'] ?: __('Responsavel nao informado') }}</div>
                                <div class="mt-2 text-sm text-amber-800">{{ trans_choice(':count alerta de estoque', $inventory['low_stock_count'], ['count' => $inventory['low_stock_count']]) }}</div>
                            </a>
                        @empty
                            <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 p-4 text-sm text-neutral-600">
                                {{ __('Sem alertas de acervo nesta frente.') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif

            @if ($navigation['canViewServing'])
                <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold text-neutral-950">{{ __('Treinamentos em que Sirvo') }}</h2>
                            <p class="text-sm text-neutral-600">{{ __('Sua frente operacional consolidada, sem depender do dashboard por role.') }}</p>
                        </div>

                        <a href="{{ route('app.portal.base.serving') }}" class="text-sm font-semibold text-sky-800">{{ __('Abrir') }}</a>
                    </div>

                    <div class="grid gap-3">
                        @forelse ($overview['serving']['upcoming'] as $training)
                            <x-app.portal.training-list-item :training="$training" />
                        @empty
                            <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 p-4 text-sm text-neutral-600">
                                {{ __('Nenhum treinamento em atuacao futura no momento.') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif

            @if ($navigation['canViewBaseEvents'])
                <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold text-neutral-950">{{ __('Eventos da Base') }}</h2>
                            <p class="text-sm text-neutral-600">{{ __('Tudo o que a sua base esta hospedando ou precisa preparar.') }}</p>
                        </div>

                        <a href="{{ route('app.portal.base.events') }}" class="text-sm font-semibold text-sky-800">{{ __('Abrir') }}</a>
                    </div>

                    <div class="grid gap-3">
                        @forelse ($overview['base_events']['upcoming'] as $training)
                            <x-app.portal.training-list-item :training="$training" />
                        @empty
                            <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 p-4 text-sm text-neutral-600">
                                {{ __('Nenhum evento futuro sediado pela sua base.') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif
        </section>

        <section class="grid gap-4 xl:grid-cols-[minmax(0,1.2fr)_minmax(20rem,0.8fr)]">
            <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-neutral-950">{{ __('Pendencias operacionais') }}</h2>
                    <p class="text-sm text-neutral-600">{{ __('Programacao, relatos e alertas que pedem acao rapida no portal.') }}</p>
                </div>

                <div class="grid gap-3">
                    @foreach ($overview['alerts']['pending_programming'] as $training)
                        <x-app.portal.training-list-item :training="$training" />
                    @endforeach

                    @foreach ($overview['alerts']['pending_reports'] as $training)
                        <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold text-neutral-950">{{ $training['title'] }}</div>
                                    <div class="text-sm text-neutral-600">{{ $training['schedule_summary'] }}</div>
                                </div>

                                <span class="rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-800">
                                    {{ __('Relatorio pendente') }}
                                </span>
                            </div>

                            @if ($training['report_route'])
                                <a href="{{ $training['report_route'] }}" class="mt-3 inline-flex text-sm font-semibold text-sky-800">
                                    {{ __('Abrir relato') }}
                                </a>
                            @endif
                        </div>
                    @endforeach

                    @foreach ($overview['alerts']['inventory'] as $inventory)
                        <a href="{{ $inventory['route'] }}" class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4 transition hover:border-sky-300 hover:bg-sky-50">
                            <div class="text-sm font-semibold text-neutral-950">{{ $inventory['name'] }}</div>
                            <div class="text-sm text-neutral-600">{{ trans_choice(':count item abaixo do minimo', $inventory['low_stock_count'], ['count' => $inventory['low_stock_count']]) }}</div>
                        </a>
                    @endforeach

                    @if ($overview['alerts']['pending_programming'] === [] && $overview['alerts']['pending_reports'] === [] && $overview['alerts']['inventory'] === [])
                        <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 p-4 text-sm text-neutral-600">
                            {{ __('Nenhuma pendencia operacional aberta agora.') }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex flex-col gap-4">
                <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                    <div class="mb-4">
                        <h2 class="text-lg font-semibold text-neutral-950">{{ __('Atalhos rapidos') }}</h2>
                        <p class="text-sm text-neutral-600">{{ __('Entradas do portal organizadas por frente de atuacao.') }}</p>
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
                        <h2 class="text-lg font-semibold text-neutral-950">{{ __('Fontes reaproveitadas') }}</h2>
                        <p class="text-sm text-neutral-600">{{ __('Leituras resumidas dos modulos operacionais ja existentes.') }}</p>
                    </div>

                    <div class="grid gap-3 text-sm text-neutral-600">
                        @if ($overview['snapshots']['teacher'])
                            <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4">
                                <div class="font-semibold text-neutral-950">{{ __('Treinamentos') }}</div>
                                <div>{{ __('Futuros: :count', ['count' => $overview['snapshots']['teacher']['future_trainings']]) }}</div>
                                <div>{{ __('Programacao pendente: :count', ['count' => $overview['snapshots']['teacher']['schedule_pendencies']]) }}</div>
                            </div>
                        @endif

                        @if ($overview['snapshots']['director'])
                            <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4">
                                <div class="font-semibold text-neutral-950">{{ __('Base e alcance') }}</div>
                                <div>{{ __('Igrejas alcancadas: :count', ['count' => $overview['snapshots']['director']['churches_reached']]) }}</div>
                                <div>{{ __('Novas igrejas: :count', ['count' => $overview['snapshots']['director']['new_churches']]) }}</div>
                            </div>
                        @endif

                        @if ($overview['snapshots']['mentor'])
                            <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4">
                                <div class="font-semibold text-neutral-950">{{ __('Mentoria') }}</div>
                                <div>{{ __('Treinamentos: :count', ['count' => $overview['snapshots']['mentor']['trainings_count']]) }}</div>
                                <div>{{ __('Sessoes concluidas: :count', ['count' => $overview['snapshots']['mentor']['completed_sessions_count']]) }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-layouts.app>
