<x-layouts.app :title="__('Dashboard do Professor')">
    @php
        $period = $dashboard['period'];
        $kpis = collect($dashboard['kpis']);
        $charts = collect($dashboard['charts']);
        $quickActions = collect($dashboard['quickActions']);
        $evangelisticImpact = collect($dashboard['evangelisticImpact']);
        $discipleshipCards = collect($dashboard['discipleship']['cards'] ?? []);
        $operational = $dashboard['operational'];

        $heroPrimary = $kpis->firstWhere('key', 'trainings_in_period');
        $heroSecondary = $kpis->whereIn('key', ['future_trainings', 'registrations', 'paid_students'])->values();

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

    <div
        class="flex h-full w-full flex-1 flex-col gap-6 rounded-[2rem] bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.12),_transparent_34%),linear-gradient(180deg,_rgba(255,255,255,0.94),_rgba(241,245,249,0.98))] p-1">
        <section
            class="overflow-hidden rounded-[1.9rem] border border-slate-200 bg-[linear-gradient(135deg,rgba(255,255,255,0.98),rgba(241,245,249,0.95))] shadow-[0_24px_80px_-48px_rgba(15,23,42,0.45)]">
            <div class="grid gap-6 px-6 py-6 lg:grid-cols-[1.35fr_0.9fr] lg:px-8 lg:py-8">
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
                            Aqui ficam os treinamentos em que você atua, os principais números do período e os pontos
                            que precisam de ação imediata.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 text-sm">
                        <span
                            class="flex flex-auto basis-28 items-center justify-center rounded-full border border-slate-200 bg-white px-3 py-1 font-medium text-slate-700">
                            Período atual: {{ $dashboard['periodLabel'] }}
                        </span>
                        <span
                            class="flex flex-auto basis-28 items-center justify-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 font-medium text-emerald-700 whitespace-nowrap">
                            Janela: {{ $dashboard['rangeLabel'] }}
                        </span>
                        <span
                            class="flex flex-auto basis-28 items-center justify-center rounded-full border border-sky-200 bg-sky-50 px-3 py-1 font-medium text-sky-700">
                            Escopo pessoal de atuação
                        </span>
                    </div>

                    <article class="flex gap-6 rounded-[1.6rem] border border-slate-200 bg-white p-5 shadow-sm">
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

                            @if ($heroSecondary->isNotEmpty())
                                <div class="mt-5 grid gap-3 sm:grid-cols-3">
                                    @foreach ($heroSecondary as $kpi)
                                        <article class="rounded-xl border border-slate-200 bg-slate-50/80 px-3 py-3">
                                            <p
                                                class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">
                                                {{ $kpi['label'] }}
                                            </p>
                                            <p class="mt-2 text-2xl font-semibold text-slate-950">
                                                {{ number_format((float) $kpi['value'], 0, ',', '.') }}
                                            </p>
                                        </article>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </article>
                </div>

                <aside class="grid gap-3 rounded-[1.7rem] border border-slate-200 bg-slate-50/80 p-4">
                    <div>
                        <h2 class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Rotina do professor
                        </h2>
                        <p class="mt-1 text-sm leading-6 text-slate-600">Organizado para mostrar agenda, prática,
                            acompanhamento e pendências com clareza.</p>
                    </div>

                    <div class="grid gap-3">
                        @foreach ($quickActions as $action)
                            <a href="{{ $action['href'] }}"
                                class="inline-flex items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 transition hover:border-sky-400 hover:text-sky-900">
                                <span>{{ $action['label'] }}</span>
                                <span class="text-sky-500">●</span>
                            </a>
                        @endforeach
                    </div>

                    <article class="rounded-2xl border border-sky-200 bg-white p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-sky-700">1. Agenda e preparo
                        </p>
                        <p class="mt-2 text-lg font-semibold text-slate-950">O que precisa de ação agora</p>
                        <p class="mt-1 text-sm leading-6 text-slate-600">Veja os próximos treinamentos e resolva as
                            pendências antes de entrar no campo.</p>
                    </article>

                    <article class="rounded-2xl border border-orange-200 bg-white p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-orange-700">2. STP</p>
                        <p class="mt-2 text-lg font-semibold text-slate-950">Prática com resultado</p>
                        <p class="mt-1 text-sm leading-6 text-slate-600">Acompanhe alcance, decisões e visitas
                            agendadas no período.</p>
                    </article>

                    <article class="rounded-2xl border border-cyan-200 bg-white p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-cyan-700">3. Continuidade</p>
                        <p class="mt-2 text-lg font-semibold text-slate-950">Discipulado e próximos passos</p>
                        <p class="mt-1 text-sm leading-6 text-slate-600">Use os dados para não perder o seguimento das
                            pessoas acompanhadas.</p>
                    </article>
                </aside>
            </div>
        </section>

        <section class="grid gap-4 xl:grid-cols-[1.15fr_1.15fr_0.8fr]">
            <article
                class="rounded-[1.7rem] border border-sky-200 bg-[linear-gradient(135deg,rgba(240,249,255,0.96),rgba(255,255,255,1))] p-5 shadow-[0_20px_60px_-42px_rgba(15,23,42,0.55)]">
                <div
                    class="flex flex-col gap-2 border-b border-sky-200/80 pb-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-sky-700">Indicadores de
                            operação</p>
                        <h2 class="mt-1 text-lg font-semibold text-slate-950">Saúde dos treinamentos</h2>
                    </div>
                    <p class="text-sm leading-5 text-slate-600">Leitura rápida do que está concluído e do que precisa
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
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-cyan-700">Discipulado paralelo
                        </p>
                        <h2 class="mt-1 text-lg font-semibold text-slate-950">Continuidade do cuidado</h2>
                    </div>

                    <div class="mt-4 grid gap-3">
                        @foreach ($discipleshipOverview as $card)
                            @php
                                $discipleshipTone = match ($card['key']) {
                                    'people_in_follow_up' => 'border-cyan-200 bg-cyan-50/90 text-cyan-900',
                                    'started' => 'border-sky-200 bg-sky-50/90 text-sky-900',
                                    'pending_follow_ups' => 'border-amber-200 bg-amber-50/90 text-amber-900',
                                    'local_church_referrals' => 'border-emerald-200 bg-emerald-50/90 text-emerald-900',
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

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
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

                <article class="rounded-[1.5rem] border p-5 shadow-sm {{ $sessionTone }}">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em]">{{ $kpi['label'] }}</p>
                    <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">
                        {{ number_format((float) $kpi['value'], 0, ',', '.') }}
                    </p>
                    <p class="mt-2 text-sm leading-5 text-slate-600">{{ $kpi['description'] }}</p>
                </article>
            @endforeach
        </section>

        @foreach ($chartSections as $section)
            <section
                class="rounded-[1.9rem] border border-slate-200 bg-white p-6 shadow-[0_20px_60px_-42px_rgba(15,23,42,0.7)]">
                <div class="border-b border-slate-200 pb-4">
                    <h2 class="text-xl font-semibold text-slate-950">{{ $section['title'] }}</h2>
                    <p class="mt-1 text-sm text-slate-600">{{ $section['description'] }}</p>
                </div>

                <div class="relative mt-5">
                    <div class="swiper js-swiper-teacher-dashboard SwiperTeacherDashboardCharts px-1 sm:px-2">
                        <div class="swiper-wrapper">
                            @foreach ($section['charts'] as $chart)
                                <div class="swiper-slide !h-auto !w-full lg:!w-[32rem] 2xl:!w-[36rem]">
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

        <section class="grid gap-6 xl:grid-cols-2">
            @foreach ($actionSections as $section)
                <article
                    class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-[0_20px_60px_-42px_rgba(15,23,42,0.65)] {{ $section['type'] === 'discipleship' ? 'xl:col-span-2' : '' }}">
                    <div class="mb-4 border-b border-slate-200 pb-4">
                        <h2 class="text-xl font-semibold text-slate-950">{{ $section['title'] }}</h2>
                        <p class="mt-1 text-sm text-slate-600">{{ $section['description'] }}</p>
                    </div>

                    <div
                        class="grid gap-3 {{ $section['type'] === 'discipleship' ? 'md:grid-cols-2 xl:grid-cols-3' : '' }}">
                        @forelse ($section['items'] as $item)
                            <article class="rounded-2xl border p-4 {{ $section['tone'] }}">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="font-semibold text-slate-950">{{ $item['label'] }}</h3>

                                        @if ($section['type'] === 'next')
                                            <p class="mt-1 text-sm text-slate-600">
                                                {{ $item['first_date'] ?? 'Data a confirmar' }}</p>
                                        @elseif ($section['type'] === 'mentor')
                                            <p class="mt-1 text-sm text-slate-600">
                                                {{ $item['mentor_shortage_context'] }}</p>
                                        @elseif ($section['type'] === 'stp')
                                            <p class="mt-1 text-sm text-slate-600">
                                                {{ $item['stp_completion_context'] }}</p>
                                        @elseif ($section['type'] === 'discipleship')
                                            <p class="mt-1 text-sm text-slate-600">{{ $item['discipleship_context'] }}
                                            </p>
                                        @endif
                                    </div>

                                    @if ($section['type'] === 'stp')
                                        <a href="{{ $item['statistics_route'] }}"
                                            class="text-sm font-semibold text-sky-700 hover:text-sky-900">Ver
                                            quadro</a>
                                    @elseif ($section['type'] === 'discipleship')
                                        <a href="{{ $item['stp_route'] }}"
                                            class="text-sm font-semibold text-sky-700 hover:text-sky-900">Ir para
                                            STP</a>
                                    @else
                                        <a href="{{ $item['route'] }}"
                                            class="text-sm font-semibold text-sky-700 hover:text-sky-900">Abrir</a>
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

                                    <div class="mt-3 flex flex-wrap gap-2 text-sm font-semibold">
                                        <a href="{{ $item['schedule_route'] }}"
                                            class="text-sky-700 hover:text-sky-900">Programação</a>
                                        <a href="{{ $item['registrations_route'] }}"
                                            class="text-sky-700 hover:text-sky-900">Inscrições</a>
                                        <a href="{{ $item['route'] }}"
                                            class="text-sky-700 hover:text-sky-900">Treinamento</a>
                                    </div>
                                @endif
                            </article>
                        @empty
                            <p class="text-sm text-slate-600">{{ $section['empty'] }}</p>
                        @endforelse
                    </div>
                </article>
            @endforeach
        </section>
    </div>
</x-layouts.app>
