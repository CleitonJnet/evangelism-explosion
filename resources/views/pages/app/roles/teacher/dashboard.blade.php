<x-layouts.app :title="__('Dashboard do Professor')">
    @php
        $period = $dashboard['period'];
        $kpis = collect($dashboard['kpis']);
        $charts = collect($dashboard['charts']);
        $quickActions = collect($dashboard['quickActions']);
        $evangelisticImpact = collect($dashboard['evangelisticImpact']);
        $discipleshipCards = collect($dashboard['discipleship']['cards'] ?? []);
        $operational = $dashboard['operational'];
        $filters = $dashboard['filters'] ?? ['startDate' => null, 'endDate' => null, 'usingCustomRange' => false];

        $heroPrimary = $kpis->firstWhere('key', 'trainings_in_period');
        $workflowKpis = $kpis->whereIn('key', ['completed_trainings', 'schedule_pendencies'])->values();

        $sessionKpis = $kpis
            ->whereIn('key', [
                'stp_sessions_planned',
                'stp_sessions_completed',
                'discipleship_sessions_planned',
                'discipleship_sessions_completed',
            ])
            ->values();

        $attentionKpis = $kpis->whereIn('key', ['church_pendencies'])->values();

        $stpOverview = $evangelisticImpact
            ->whereIn('key', ['gospel_explained', 'people_reached', 'decisions', 'scheduled_visits'])
            ->values();

        $discipleshipOverview = $discipleshipCards
            ->whereIn('key', ['people_in_follow_up', 'started', 'pending_follow_ups', 'local_church_referrals'])
            ->values();

        $chartSections = [
            [
                'title' => 'Ritmo da operação',
                'description' => 'Mostra o volume do período e o andamento dos treinamentos.',
                'layout' => 'swiper',
                'charts' => $charts
                    ->whereIn('id', ['teacher-registrations-line', 'teacher-trainings-status'])
                    ->values(),
            ],
            [
                'title' => 'Resultados ministeriais',
                'description' => 'Consolida os resultados da prática e do acompanhamento.',
                'layout' => 'swiper',
                'charts' => $charts->whereIn('id', ['teacher-stp-results', 'teacher-discipleship-results'])->values(),
            ],
            [
                'title' => 'Base e origem',
                'description' => 'Mostra a situação financeira e de onde vêm os alunos.',
                'layout' => 'swiper',
                'charts' => $charts->whereIn('id', ['teacher-financial-status', 'teacher-church-ranking'])->values(),
            ],
        ];

        $actionSections = [
            [
                'title' => 'Agenda imediata',
                'description' => 'Treinamentos que exigem preparação e acompanhamento mais próximo.',
                'items' => $operational['nextTrainings'],
                'empty' => 'Nenhum treinamento futuro dentro da janela selecionada.',
                'tone' => 'border-sky-200 bg-sky-50/80',
                'type' => 'next',
            ],
            [
                'title' => 'Pendências operacionais',
                'description' => 'Programação, validação e consistência do registro do treinamento.',
                'items' => $operational['pendingTrainings'],
                'empty' => 'Sem pendências operacionais no momento.',
                'tone' => 'border-amber-200 bg-amber-50/80',
                'type' => 'pending',
            ],
            [
                'title' => 'Cobertura de mentoria',
                'description' => 'Treinamentos abaixo da cobertura mínima para acompanhar os alunos.',
                'items' => $operational['mentorShortageTrainings'],
                'empty' => 'Nenhum treinamento está abaixo da cobertura mínima de mentoria.',
                'tone' => 'border-rose-200 bg-rose-50/80',
                'type' => 'mentor',
            ],
            [
                'title' => 'STP com baixa conclusão',
                'description' => 'Treinamentos cuja prática está abaixo do esperado.',
                'items' => $operational['lowStpCompletionTrainings'],
                'empty' => 'As sessões STP estão com conclusão saudável na janela atual.',
                'tone' => 'border-orange-200 bg-orange-50/80',
                'type' => 'stp',
            ],
            [
                'title' => 'Discipulado sem continuidade',
                'description' => 'Casos que precisam de próximo passo, follow-up ou encaminhamento.',
                'items' => $operational['discipleshipWithoutContinuityTrainings'],
                'empty' => 'Nenhum treinamento com indício de ruptura na continuidade do discipulado.',
                'tone' => 'border-cyan-200 bg-cyan-50/80',
                'type' => 'discipleship',
            ],
        ];

        $actionNavigation = [
            [
                'label' => 'Visão geral',
                'target' => '#teacher-dashboard-hero',
                'description' => 'KPIs e leitura do período.',
            ],
            [
                'label' => 'Operação',
                'target' => '#teacher-dashboard-operation',
                'description' => 'Saúde dos treinamentos e agenda.',
            ],
            [
                'label' => 'Sessões',
                'target' => '#teacher-dashboard-sessions',
                'description' => 'STP e discipulado em andamento.',
            ],
            [
                'label' => 'Gráficos',
                'target' => '#teacher-dashboard-charts',
                'description' => 'Tendências e distribuição.',
            ],
        ];

        $prioritySignals = collect($actionSections)
            ->map(
                fn(array $section): array => [
                    'label' => $section['title'],
                    'count' => count($section['items']),
                    'type' => $section['type'],
                ],
            )
            ->filter(fn(array $signal): bool => $signal['count'] > 0)
            ->values();
    @endphp

    @once
        @push('css')
            <style>
                .js-swiper-teacher-dashboard .swiper-button-next,
                .js-swiper-teacher-dashboard .swiper-button-prev {
                    color: #b79a32;
                }

                .js-swiper-teacher-dashboard .swiper-button-next:after,
                .js-swiper-teacher-dashboard .swiper-button-prev:after {
                    font-size: 16px;
                    font-weight: 900;
                }

                .js-swiper-teacher-dashboard .swiper-pagination-bullet {
                    background: #c7a840;
                    opacity: .35;
                }

                .js-swiper-teacher-dashboard .swiper-pagination-bullet-active {
                    background: #f1d57a;
                    opacity: 1;
                }

                .teacher-actions-scroll {
                    scrollbar-color: rgba(148, 163, 184, 0.45) rgba(15, 23, 42, 0.96);
                }

                .teacher-actions-scroll::-webkit-scrollbar {
                    width: 10px;
                }

                .teacher-actions-scroll::-webkit-scrollbar-track {
                    background: rgba(15, 23, 42, 0.96);
                }

                .teacher-actions-scroll::-webkit-scrollbar-thumb {
                    background: rgba(148, 163, 184, 0.45);
                    border-radius: 9999px;
                    border: 2px solid rgba(15, 23, 42, 0.96);
                }
            </style>
        @endpush

        @push('js')
            <script>
                (function() {
                    function initTeacherDashboardSwipers() {
                        document.querySelectorAll('.SwiperTeacherDashboardCharts').forEach((root) => {
                            if (root.dataset.swiperInit === '1') {
                                return;
                            }

                            root.dataset.swiperInit = '1';

                            const nextEl = root.querySelector('.swiper-button-next');
                            const prevEl = root.querySelector('.swiper-button-prev');
                            const paginationEl = root.querySelector('.swiper-pagination');
                            const slidesCount = root.querySelectorAll('.swiper-slide').length;

                            if (slidesCount <= 1) {
                                nextEl?.classList.add('hidden');
                                prevEl?.classList.add('hidden');
                                paginationEl?.classList.add('hidden');
                            }

                            new Swiper(root, {
                                slidesPerView: 'auto',
                                spaceBetween: 16,
                                loop: false,
                                grabCursor: true,
                                autoplay: false,
                                navigation: {
                                    nextEl,
                                    prevEl
                                },
                                pagination: {
                                    el: paginationEl,
                                    clickable: true,
                                    dynamicBullets: true,
                                },
                            });
                        });
                    }

                    document.addEventListener('DOMContentLoaded', initTeacherDashboardSwipers);
                    document.addEventListener('livewire:navigated', initTeacherDashboardSwipers);
                })
                ();
            </script>
        @endpush
    @endonce

    <div x-data="{ actionsOpen: true }" class="relative">
        <div class="flex h-full w-full flex-1 flex-col gap-6">
            <section id="teacher-dashboard-hero"
                class="overflow-hidden rounded-[1.9rem] border border-slate-200 bg-[linear-gradient(135deg,rgba(255,255,255,0.98),rgba(241,245,249,0.95))] shadow-[0_24px_80px_-48px_rgba(15,23,42,0.45)]">
                <div class="grid gap-6 px-4 py-5 sm:px-6 sm:py-6 lg:grid-cols-[1.35fr_0.9fr] lg:px-8 lg:py-8">
                    <div class="space-y-6">
                        <div
                            class="inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-sky-800">
                            Dashboard do Professor
                        </div>

                        <div class="space-y-3">
                            <h1 class="max-w-4xl text-3xl font-semibold tracking-tight text-slate-950 lg:text-[2.2rem]">
                                Operação do campo, STP e discipulado em uma visão clara para agir com rapidez.
                            </h1>
                            <p class="max-w-3xl text-sm leading-6 text-slate-600">
                                Aqui ficam os treinamentos em que você atua, os principais números do período e os
                                pontos
                                que precisam de ação imediata.
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-3 text-sm">
                            <span
                                class="flex flex-auto basis-28 items-center justify-center rounded-full border border-sky-200 bg-sky-50 px-3 py-1 font-medium text-sky-700">
                                Escopo pessoal de atuação
                            </span>
                            <span
                                class="flex flex-auto basis-28 items-center justify-center rounded-full border border-slate-200 bg-white px-3 py-1 font-medium text-slate-700">
                                Período atual: {{ $dashboard['periodLabel'] }}
                            </span>
                            <span
                                class="flex flex-auto basis-28 items-center justify-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 font-medium text-emerald-700 whitespace-nowrap">
                                Janela: {{ $dashboard['rangeLabel'] }}
                            </span>
                        </div>

                        <article
                            class="flex flex-col gap-5 rounded-[1.6rem] border border-slate-200 bg-white p-4 shadow-sm sm:p-5 xl:flex-row xl:items-start xl:gap-6">
                            <div class="flex-auto">
                                @if ($heroPrimary)
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
                                        {{ $heroPrimary['label'] }}
                                    </p>
                                    <p class="mt-3 text-5xl font-semibold tracking-tight text-slate-950">
                                        {{ number_format((float) $heroPrimary['value'], 0, ',', '.') }}
                                    </p>
                                    <p class="mt-3 max-w-md text-sm leading-6 text-slate-600">
                                        {{ $heroPrimary['description'] }}
                                    </p>
                                @endif

                                <div class="mt-5 flex flex-wrap gap-2">
                                    @foreach ($dashboard['periodOptions'] as $option)
                                        <a href="{{ route('app.teacher.dashboard', ['period' => $option['value']]) }}"
                                            class="flex flex-auto basis-20 items-center justify-center rounded-xl border px-4 py-2 text-sm font-semibold transition {{ $period->value === $option['value'] ? 'border-sky-950 bg-sky-950 text-white shadow-sm' : 'border-slate-200 bg-white text-slate-600 hover:border-sky-300 hover:text-sky-900' }}">
                                            {{ $option['label'] }}
                                        </a>
                                    @endforeach
                                </div>


                            </div>

                            <form method="GET" action="{{ route('app.teacher.dashboard') }}"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 p-4 xl:mt-5 xl:max-w-md xl:flex-auto xl:basis-80">
                                <div class="flex flex-col gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">Filtrar por datas exatas</p>
                                        <p class="mt-1 text-sm text-slate-600">Use este campo quando quiser consultar um
                                            começo e um fim específicos.</p>
                                    </div>

                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <label class="grid gap-1">
                                            <span class="text-sm font-medium text-slate-700">Data inicial</span>
                                            <input type="date" name="start_date" value="{{ $filters['startDate'] }}"
                                                class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-slate-500">
                                        </label>

                                        <label class="grid gap-1">
                                            <span class="text-sm font-medium text-slate-700">Data final</span>
                                            <input type="date" name="end_date" value="{{ $filters['endDate'] }}"
                                                class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-slate-500">
                                        </label>
                                    </div>

                                    <div class="flex flex-wrap gap-2 justify-end">
                                        <button type="submit"
                                            class="inline-flex items-center justify-center rounded-xl bg-slate-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                                            Aplicar datas
                                        </button>

                                        @if ($filters['usingCustomRange'])
                                            <a href="{{ route('app.teacher.dashboard', ['period' => $period->value]) }}"
                                                class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:text-slate-950">
                                                Limpar datas
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </form>
                        </article>
                    </div>

                    <aside
                        class="grid gap-3 rounded-[1.7rem] border border-slate-200 bg-slate-50/80 p-4 sm:grid-cols-3 lg:grid-cols-1">
                        <div>
                            <h2 class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Radar de
                                execução
                            </h2>
                            <p class="mt-1 text-sm leading-6 text-slate-600">A leitura principal ficou limpa. As ações
                                ficam concentradas na coluna fixa para você agir sem perder o contexto.</p>
                        </div>

                        <article class="rounded-2xl border border-sky-200 bg-white p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-sky-700">Fluxo inteligente
                            </p>
                            <p class="mt-2 text-lg font-semibold text-slate-950">Comece pelo que trava a operação</p>
                            <p class="mt-1 text-sm leading-6 text-slate-600">Use a coluna fixa para resolver agenda,
                                validação, STP e continuidade conforme a prioridade do dia.</p>
                        </article>

                        <article class="rounded-2xl border border-orange-200 bg-white p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-orange-700">Leitura
                                executiva
                            </p>
                            <p class="mt-2 text-lg font-semibold text-slate-950">Dados para decidir rápido</p>
                            <p class="mt-1 text-sm leading-6 text-slate-600">O centro da tela mostra performance. A
                                lateral
                                concentra os links para execução imediata.</p>
                        </article>

                        <article class="rounded-2xl border border-cyan-200 bg-white p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-cyan-700">Tela mais limpa
                            </p>
                            <p class="mt-2 text-lg font-semibold text-slate-950">Menos ruído, mais foco</p>
                            <p class="mt-1 text-sm leading-6 text-slate-600">As pendências operacionais saíram do corpo
                                do
                                dashboard e passaram a viver em um painel fixo de ação.</p>
                        </article>
                    </aside>
                </div>
            </section>

            <section id="teacher-dashboard-operation"
                class="grid gap-4 lg:grid-cols-2 xl:grid-cols-[1.15fr_1.15fr_0.8fr]">
                <article
                    class="rounded-[1.7rem] border border-sky-200 bg-[linear-gradient(135deg,rgba(240,249,255,0.96),rgba(255,255,255,1))] p-5 shadow-[0_20px_60px_-42px_rgba(15,23,42,0.55)]">
                    <div
                        class="flex flex-col gap-2 border-b border-sky-200/80 pb-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-sky-700">Indicadores de
                                operação</p>
                            <h2 class="mt-1 text-lg font-semibold text-slate-950">Saúde dos treinamentos</h2>
                        </div>
                        <p class="text-sm leading-5 text-slate-600">Leitura rápida do que está concluído e do que
                            precisa
                            de atenção.</p>
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach ($workflowKpis as $kpi)
                            @php
                                $workflowTone = match ($kpi['key']) {
                                    'completed_trainings' => 'border-emerald-200 bg-emerald-50/90 text-emerald-900',
                                    'schedule_pendencies' => 'border-amber-200 bg-amber-50/90 text-amber-900',
                                    default => 'border-slate-200 bg-slate-50 text-slate-900',
                                };
                            @endphp

                            <div class="rounded-2xl border px-4 py-3 shadow-sm {{ $workflowTone }}">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-sm font-semibold uppercase tracking-[0.08em]">
                                        {{ $kpi['label'] }}
                                    </span>
                                    <span class="text-2xl font-semibold">
                                        {{ number_format((float) $kpi['value'], 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </article>

                <article
                    class="rounded-[1.7rem] border border-orange-200 bg-[linear-gradient(135deg,rgba(255,247,237,0.96),rgba(255,255,255,1))] p-5 shadow-[0_20px_60px_-42px_rgba(15,23,42,0.55)]">
                    <div
                        class="flex flex-col gap-2 border-b border-orange-200/80 pb-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-orange-700">Impacto
                                evangelístico</p>
                            <h2 class="mt-1 text-lg font-semibold text-slate-950">Resultados STP</h2>
                        </div>
                        <p class="text-sm leading-5 text-slate-600">Resumo da prática e dos retornos do período.</p>
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach ($stpOverview as $card)
                            @php
                                $stpTone = match ($card['key']) {
                                    'gospel_explained' => 'border-orange-200 bg-orange-50/90 text-orange-900',
                                    'people_reached' => 'border-amber-200 bg-amber-50/90 text-amber-900',
                                    'decisions' => 'border-emerald-200 bg-emerald-50/90 text-emerald-900',
                                    'scheduled_visits' => 'border-sky-200 bg-sky-50/90 text-sky-900',
                                    default => 'border-slate-200 bg-slate-50 text-slate-900',
                                };
                            @endphp

                            <div class="rounded-2xl border px-4 py-3 shadow-sm {{ $stpTone }}">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-sm font-semibold uppercase tracking-[0.08em]">
                                        {{ $card['label'] }}
                                    </span>
                                    <span class="text-2xl font-semibold">
                                        {{ number_format((float) $card['value'], 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </article>

                <div class="grid gap-4">
                    <article
                        class="rounded-[1.7rem] border border-cyan-200 bg-[linear-gradient(180deg,rgba(236,254,255,0.92),rgba(255,255,255,1))] p-5 shadow-[0_20px_60px_-42px_rgba(15,23,42,0.45)]">
                        <div class="border-b border-cyan-200/80 pb-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-cyan-700">Discipulado
                                paralelo</p>
                            <h2 class="mt-1 text-lg font-semibold text-slate-950">Continuidade do cuidado</h2>
                        </div>

                        <div class="mt-4 grid gap-3">
                            @foreach ($discipleshipOverview as $card)
                                @php
                                    $discipleshipTone = match ($card['key']) {
                                        'people_in_follow_up' => 'border-cyan-200 bg-cyan-50/90 text-cyan-900',
                                        'started' => 'border-sky-200 bg-sky-50/90 text-sky-900',
                                        'pending_follow_ups' => 'border-amber-200 bg-amber-50/90 text-amber-900',
                                        'local_church_referrals'
                                            => 'border-emerald-200 bg-emerald-50/90 text-emerald-900',
                                        default => 'border-slate-200 bg-slate-50 text-slate-900',
                                    };
                                @endphp

                                <div class="rounded-2xl border px-4 py-3 shadow-sm {{ $discipleshipTone }}">
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="text-sm font-semibold uppercase tracking-[0.08em]">
                                            {{ $card['label'] }}
                                        </span>
                                        <span class="text-2xl font-semibold">
                                            {{ number_format((float) $card['value'], 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </article>

                    @foreach ($attentionKpis as $kpi)
                        <article
                            class="rounded-[1.7rem] border border-amber-200 bg-[linear-gradient(180deg,rgba(255,251,235,0.94),rgba(255,255,255,1))] p-5 shadow-[0_20px_60px_-42px_rgba(15,23,42,0.45)]">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">
                                {{ $kpi['label'] }}
                            </p>
                            <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">
                                {{ number_format((float) $kpi['value'], 0, ',', '.') }}
                            </p>
                            <p class="mt-3 text-sm leading-5 text-slate-600">
                                {{ $kpi['description'] }}
                            </p>
                        </article>
                    @endforeach
                </div>
            </section>

            <section id="teacher-dashboard-sessions" class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @foreach ($sessionKpis as $kpi)
                    @php
                        $sessionTone = match ($kpi['key']) {
                            'stp_sessions_planned' => 'border-orange-200 bg-orange-50/70 text-orange-800',
                            'stp_sessions_completed' => 'border-emerald-200 bg-emerald-50/70 text-emerald-800',
                            'discipleship_sessions_planned' => 'border-cyan-200 bg-cyan-50/70 text-cyan-800',
                            'discipleship_sessions_completed' => 'border-sky-200 bg-sky-50/70 text-sky-800',
                            default => 'border-slate-200 bg-white text-slate-600',
                        };
                    @endphp

                    <article class="rounded-3xl border p-5 shadow-sm {{ $sessionTone }}">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em]">{{ $kpi['label'] }}</p>
                        <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">
                            {{ number_format((float) $kpi['value'], 0, ',', '.') }}
                        </p>
                        <p class="mt-2 text-sm leading-5 text-slate-600">{{ $kpi['description'] }}</p>
                    </article>
                @endforeach
            </section>

            <div id="teacher-dashboard-charts" class="contents">
                @foreach ($chartSections as $section)
                    <section
                        class="rounded-[1.9rem] border border-slate-200 bg-white p-4 shadow-[0_20px_60px_-42px_rgba(15,23,42,0.7)] sm:p-6">
                        <div class="border-b border-slate-200 pb-4">
                            <h2 class="text-xl font-semibold text-slate-950">{{ $section['title'] }}</h2>
                            <p class="mt-1 text-sm text-slate-600">{{ $section['description'] }}</p>
                        </div>

                        <div class="relative mt-5">
                            <div class="swiper js-swiper-teacher-dashboard SwiperTeacherDashboardCharts px-1 sm:px-2">
                                <div class="swiper-wrapper">
                                    @foreach ($section['charts'] as $chart)
                                        <div class="swiper-slide h-auto! w-full! sm:w-[34rem]! 2xl:w-xl!">
                                            <x-dashboard.chart :chart="$chart" />
                                        </div>
                                    @endforeach
                                </div>

                                <div class="swiper-button-prev left-0 sm:-left-1"></div>
                                <div class="swiper-button-next right-0 sm:-right-1"></div>
                                <div class="swiper-pagination relative! mt-6!"></div>
                            </div>
                        </div>
                    </section>
                @endforeach
            </div>
        </div>

        <div x-data="{ open: false }"
            class="fixed inset-x-2 bottom-0 z-40 w-auto sm:right-3 sm:left-auto sm:w-[calc(100vw-1.5rem)] sm:max-w-md">
            <article x-on:click.outside="open = false"
                class="overflow-hidden rounded-t-xl border border-sky-900 bg-sky-950 text-white shadow-[0_24px_70px_-36px_rgba(15,23,42,0.95)]">
                <button type="button" x-on:click="open = !open"
                    class="flex w-full items-center justify-between gap-3 border-b border-white/10 bg-[#06233a] px-4 py-2 text-left transition hover:bg-[#041a2b]">
                    <div class="flex min-w-0 items-center gap-3">
                        <span
                            class="inline-flex size-7 shrink-0 items-center justify-center rounded-lg border border-amber-300/40 text-amber-200 shadow-[0_0_18px_rgba(251,191,36,0.22)]">
                            <flux:icon.exclamation-triangle class="size-3.5" />
                        </span>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-300">Ações do
                                professor</p>
                            <p class="text-xs text-slate-400">Agenda, pendências e prioridades para agir agora.</p>
                        </div>
                    </div>

                    <span
                        class="inline-flex size-7 shrink-0 items-center justify-center rounded-full border border-white/10 bg-white/10 text-slate-200">
                        <flux:icon.chevron-down class="size-3.5 transition duration-200"
                            x-bind:class="open ? '' : 'rotate-180'" />
                    </span>
                </button>

                <div x-cloak x-show="open" x-collapse
                    class="teacher-actions-scroll max-h-[65vh] overflow-y-auto bg-sky-950 px-4 py-4 sm:max-h-[70vh]">
                    <div class="mb-4">
                        <h2 class="text-lg font-semibold">Ações e pendências</h2>
                        <p class="mt-1 text-sm text-slate-300">Aqui ficam os atalhos e alertas operacionais do
                            professor, no mesmo padrão visual da diretoria.</p>
                    </div>

                    <div class="space-y-5">
                        <section>
                            <div>
                                <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-300">Navegação
                                </h3>
                                <p class="mt-1 text-sm text-slate-400">Acesse rápido os blocos principais do dashboard.
                                </p>
                            </div>

                            <div class="mt-3 grid gap-2">
                                @foreach ($actionNavigation as $link)
                                    <a href="{{ $link['target'] }}"
                                        class="block rounded-xl border border-sky-800 bg-sky-900/40 px-4 py-3 text-sm text-slate-100 transition hover:bg-white/90 hover:text-slate-900">
                                        <p class="font-semibold">{{ $link['label'] }}</p>
                                        <p class="mt-1 text-sm opacity-80">{{ $link['description'] }}</p>
                                    </a>
                                @endforeach
                            </div>
                        </section>

                        @if ($quickActions->isNotEmpty())
                            <section>
                                <div>
                                    <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-300">Ações
                                        rápidas</h3>
                                    <p class="mt-1 text-sm text-slate-400">Entradas frequentes para a rotina do
                                        professor.</p>
                                </div>

                                <div class="mt-3 grid gap-2">
                                    @foreach ($quickActions as $action)
                                        <a href="{{ $action['href'] }}"
                                            class="block rounded-xl border border-cyan-200 bg-cyan-50/85 px-4 py-3 text-sm text-cyan-950 transition hover:bg-white/90">
                                            <div class="flex items-center justify-between gap-3">
                                                <span class="font-semibold">{{ $action['label'] }}</span>
                                                <span
                                                    class="shrink-0 rounded-full bg-white/70 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-800">
                                                    Ir
                                                </span>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        @foreach ($actionSections as $section)
                            <section>
                                <div>
                                    <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-300">
                                        {{ $section['title'] }}</h3>
                                    <p class="mt-1 text-sm text-slate-400">{{ $section['description'] }}</p>
                                </div>

                                <div class="mt-3 grid gap-2">
                                    @forelse ($section['items'] as $item)
                                        <article class="rounded-xl border px-4 py-3 text-sm {{ $section['tone'] }}">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <p class="font-semibold text-slate-950">{{ $item['label'] }}</p>

                                                    @if ($section['type'] === 'next')
                                                        <p class="mt-1 text-sm text-slate-700">
                                                            {{ $item['first_date'] ?? 'Data a confirmar' }}</p>
                                                    @elseif ($section['type'] === 'mentor')
                                                        <p class="mt-1 text-sm text-slate-700">
                                                            {{ $item['mentor_shortage_context'] }}</p>
                                                    @elseif ($section['type'] === 'stp')
                                                        <p class="mt-1 text-sm text-slate-700">
                                                            {{ $item['stp_completion_context'] }}</p>
                                                    @elseif ($section['type'] === 'discipleship')
                                                        <p class="mt-1 text-sm text-slate-700">
                                                            {{ $item['discipleship_context'] }}</p>
                                                    @endif
                                                </div>

                                                @if ($section['type'] === 'stp')
                                                    <a href="{{ $item['statistics_route'] }}"
                                                        class="shrink-0 rounded-full bg-white/70 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-800">
                                                        Abrir
                                                    </a>
                                                @elseif ($section['type'] === 'discipleship')
                                                    <a href="{{ $item['stp_route'] }}"
                                                        class="shrink-0 rounded-full bg-white/70 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-800">
                                                        STP
                                                    </a>
                                                @else
                                                    <a href="{{ $item['route'] }}"
                                                        class="shrink-0 rounded-full bg-white/70 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-800">
                                                        Abrir
                                                    </a>
                                                @endif
                                            </div>

                                            @if ($section['type'] === 'pending')
                                                <div class="mt-3 flex flex-wrap gap-2">
                                                    @if ($item['has_schedule_issue'])
                                                        <span
                                                            class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-200">
                                                            Programação pendente
                                                        </span>
                                                    @endif
                                                    @if ($item['has_registration_issue'])
                                                        <span
                                                            class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-200">
                                                            Validação/igreja pendente
                                                        </span>
                                                    @endif
                                                </div>

                                                <div
                                                    class="mt-3 flex flex-wrap gap-2 text-xs font-semibold uppercase tracking-[0.12em] text-slate-700">
                                                    <a href="{{ $item['schedule_route'] }}">Programação</a>
                                                    <a href="{{ $item['registrations_route'] }}">Inscrições</a>
                                                    <a href="{{ $item['route'] }}">Treinamento</a>
                                                </div>
                                            @endif
                                        </article>
                                    @empty
                                        <p class="text-sm text-slate-400">{{ $section['empty'] }}</p>
                                    @endforelse
                                </div>
                            </section>
                        @endforeach
                    </div>
                </div>
            </article>
        </div>
    </div>
</x-layouts.app>
