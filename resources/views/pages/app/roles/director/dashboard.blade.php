<x-layouts.app :title="__('Raio-X Nacional')">
    @php
        $period = $dashboard['period'];
        $kpis = collect($dashboard['kpis']);
        $charts = collect($dashboard['charts']);
        $filters = $dashboard['filters'] ?? ['startDate' => null, 'endDate' => null, 'usingCustomRange' => false];
        $eventStatusOverview = collect($dashboard['eventStatusOverview'] ?? []);

        $executiveKpis = $kpis
            ->whereIn('key', ['trainings', 'registrations', 'paid_students', 'payment_rate'])
            ->values();

        $expansionKpis = $kpis
            ->whereIn('key', ['churches_reached', 'new_churches', 'active_teachers', 'pastors_trained'])
            ->values();

        $impactKpis = $kpis
            ->whereIn('key', ['gospel_explained', 'people_reached', 'decisions', 'discipleship'])
            ->values();

        $balanceKpis = $kpis->whereIn('key', ['completed_trainings'])->values();

        $stpOverview = $kpis
            ->whereIn('key', ['gospel_explained', 'people_reached', 'decisions', 'scheduled_visits'])
            ->values();

        $growthOverview = $kpis->whereIn('key', ['registrations', 'pastors_trained', 'new_churches'])->values();

        $heroPrimary = $executiveKpis->first();
        $heroSecondary = $executiveKpis->slice(1)->values();
        $leadershipTeachers = collect($dashboard['leadershipTeachers']);
        $activeLeadershipTeachers = $leadershipTeachers->where('is_active', true)->values();
        $inactiveLeadershipTeachers = $leadershipTeachers->where('is_active', false)->values();
        $leadershipCourseFilters = $leadershipTeachers
            ->flatMap(fn (array $teacher) => collect($teacher['courses'] ?? []))
            ->unique('id')
            ->sortBy('name')
            ->values();

        $chartSections = [
            [
                'title' => 'Ritmo nacional',
                'description' => 'Evolução da operação ao longo da janela selecionada.',
                'layout' => 'swiper',
                'charts' => $charts
                    ->whereIn('id', [
                        'director-trainings-month',
                        'director-registrations-month',
                        'director-decisions-month',
                        'director-new-churches-month',
                    ])
                    ->values(),
            ],
            [
                'title' => 'Distribuição da operação',
                'description' => 'Concentração por curso, estado, professor e igreja.',
                'layout' => 'swiper',
                'charts' => $charts
                    ->whereIn('id', [
                        'director-distribution-course',
                        'director-distribution-state',
                        'director-ranking-teachers',
                        'director-ranking-churches',
                    ])
                    ->values(),
            ],
        ];

        $governanceSections = [
            [
                'title' => 'Cobertura regional',
                'description' => 'Locais com baixa presença nacional na janela atual.',
                'items' => $dashboard['alerts']['regions'],
                'empty' => 'Sem regiões críticas identificadas na janela atual.',
                'tone' => 'border-amber-200 bg-amber-50/85 text-amber-900',
                'render' => fn(array $item): string => "{$item['label']} · {$item['context']}",
            ],
            [
                'title' => 'Risco operacional',
                'description' => 'Treinamentos que exigem revisão imediata.',
                'items' => $dashboard['alerts']['operationalRisks'],
                'empty' => 'Sem treinamentos críticos no momento.',
                'tone' => 'border-rose-200 bg-rose-50/85 text-rose-900',
                'render' => fn(array $item): string => "{$item['label']} · {$item['context']}",
            ],
            [
                'title' => 'Saúde financeira',
                'description' => 'Eventos abaixo do patamar mínimo de conversão.',
                'items' => $dashboard['alerts']['financialBottlenecks'],
                'empty' => 'Nenhum gargalo financeiro abaixo do limiar configurado.',
                'tone' => 'border-orange-200 bg-orange-50/85 text-orange-900',
                'render' => fn(array $item): string => "{$item['label']} · {$item['payment_rate']}% de pagamento",
            ],
            [
                'title' => 'Capacidade docente',
                'description' => 'Concentração excessiva em professores titulares.',
                'items' => $dashboard['alerts']['overloadedTeachers'],
                'empty' => 'Sem concentração crítica de carga entre professores titulares.',
                'tone' => 'border-cyan-200 bg-cyan-50/85 text-cyan-900',
                'render' => fn(array $item): string => "{$item['label']} · {$item['value']} treinamentos como titular",
            ],
            [
                'title' => 'Recorrência de cursos',
                'description' => 'Cobertura irregular ou baixa repetição.',
                'items' => $dashboard['alerts']['lowRecurrenceCourses'],
                'empty' => 'Sem baixa recorrência relevante na janela atual.',
                'tone' => 'border-slate-200 bg-slate-50/85 text-slate-800',
                'render' => fn(array $item): string => "{$item['label']} · {$item['context']}",
            ],
        ];
    @endphp

    @once
        @push('css')
            <style>
                .js-swiper-director-national .swiper-button-next,
                .js-swiper-director-national .swiper-button-prev {
                    color: #b79a32;
                }

                .js-swiper-director-national .swiper-button-next:after,
                .js-swiper-director-national .swiper-button-prev:after {
                    font-size: 16px;
                    font-weight: 900;
                }

                .js-swiper-director-national .swiper-pagination-bullet {
                    background: #c7a840;
                    opacity: .35;
                }

                .js-swiper-director-national .swiper-pagination-bullet-active {
                    background: #f1d57a;
                    opacity: 1;
                }

                .director-governance-scroll {
                    scrollbar-color: rgba(148, 163, 184, 0.55) #082f49;
                }

                .director-governance-scroll::-webkit-scrollbar {
                    width: 10px;
                }

                .director-governance-scroll::-webkit-scrollbar-track {
                    background: #082f49;
                }

                .director-governance-scroll::-webkit-scrollbar-thumb {
                    background: rgba(148, 163, 184, 0.55);
                    border-radius: 9999px;
                    border: 2px solid #082f49;
                }
            </style>
        @endpush

        @push('js')
            <script>
                (function() {
                    function initDirectorNationalSwipers() {
                        document.querySelectorAll('.SwiperDirectorNationalCharts').forEach((root) => {
                            if (root.dataset.swiperInit === '1') {
                                return;
                            }

                            root.dataset.swiperInit = '1';

                            const nextEl = root.querySelector('.swiper-button-next');
                            const prevEl = root.querySelector('.swiper-button-prev');
                            const paginationEl = root.querySelector('.swiper-pagination');
                            const slidesCount = root.querySelectorAll('.swiper-slide').length;
                            const isSingleSlide = slidesCount <= 1;

                            if (isSingleSlide) {
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

                    window.directorTeacherDirectory = function(payload) {
                        return {
                            activeTeachers: payload.activeTeachers ?? [],
                            inactiveTeachers: payload.inactiveTeachers ?? [],
                            courseFilters: payload.courseFilters ?? [],
                            selectedCourseId: null,
                            sortColumn: 'name',
                            sortDirection: 'asc',
                            setCourseFilter(courseId) {
                                this.selectedCourseId = this.selectedCourseId === courseId ? null : courseId;
                            },
                            toggleSort(column) {
                                if (this.sortColumn === column) {
                                    this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';

                                    return;
                                }

                                this.sortColumn = column;
                                this.sortDirection = column === 'trainings' ? 'desc' : 'asc';
                            },
                            isSorted(column) {
                                return this.sortColumn === column;
                            },
                            sortIndicator(column) {
                                if (!this.isSorted(column)) {
                                    return '↕';
                                }

                                return this.sortDirection === 'asc' ? '↑' : '↓';
                            },
                            activeTeachersView() {
                                return this.sortTeachers(this.filterTeachers(this.activeTeachers));
                            },
                            inactiveTeachersView() {
                                return this.sortTeachers(this.filterTeachers(this.inactiveTeachers));
                            },
                            filterTeachers(teachers) {
                                if (this.selectedCourseId === null) {
                                    return teachers;
                                }

                                return teachers.filter((teacher) => (teacher.courses ?? []).some((course) => Number(course.id) === Number(this.selectedCourseId)));
                            },
                            sortTeachers(teachers) {
                                const direction = this.sortDirection === 'asc' ? 1 : -1;

                                return [...teachers].sort((firstTeacher, secondTeacher) => {
                                    const comparison = this.compareValues(this.sortValue(firstTeacher), this.sortValue(secondTeacher));

                                    if (comparison !== 0) {
                                        return comparison * direction;
                                    }

                                    return this.compareValues(firstTeacher.name ?? '', secondTeacher.name ?? '') * direction;
                                });
                            },
                            sortValue(teacher) {
                                switch (this.sortColumn) {
                                    case 'location':
                                        return [teacher.state, teacher.city, teacher.church_name].filter(Boolean).join(' ');
                                    case 'trainings':
                                        return Number(teacher.principal_trainings_count ?? 0) + Number(teacher.assistant_trainings_count ?? 0);
                                    case 'courses':
                                        return (teacher.courses ?? []).map((course) => `${course.type ?? ''} ${course.name ?? ''}`.trim()).join(' | ');
                                    case 'name':
                                    default:
                                        return teacher.name ?? '';
                                }
                            },
                            compareValues(firstValue, secondValue) {
                                if (typeof firstValue === 'number' && typeof secondValue === 'number') {
                                    return firstValue - secondValue;
                                }

                                return String(firstValue ?? '').localeCompare(String(secondValue ?? ''), 'pt-BR', {
                                    sensitivity: 'base',
                                });
                            },
                        };
                    };

                    document.addEventListener('DOMContentLoaded', initDirectorNationalSwipers);
                    document.addEventListener('livewire:navigated', initDirectorNationalSwipers);
                })
                ();
            </script>
        @endpush
    @endonce

    <section
        class="mb-6 overflow-hidden rounded-[2rem] border border-slate-200 bg-[linear-gradient(135deg,rgba(255,255,255,0.98),rgba(241,245,249,0.95))] shadow-[0_24px_80px_-48px_rgba(15,23,42,0.45)]">
        <div class="grid gap-6 px-4 py-5 sm:px-6 sm:py-6 lg:grid-cols-[1.4fr_0.9fr] lg:px-8 lg:py-8">
            <div class="space-y-6">
                <div
                    class="inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.26em] text-sky-800">
                    Raio-X Nacional
                </div>

                <div class="space-y-3">
                    <h1 class="max-w-4xl text-3xl font-semibold tracking-tight text-slate-950 lg:text-[2.25rem]">
                        Visão nacional, operacional e ministerial consolidada do EE Brasil.
                    </h1>
                    <p class="max-w-3xl text-sm leading-6 text-slate-600">
                        Aqui estão os números mais importantes do período, organizados de forma simples para apoiar
                        decisões com segurança e clareza.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3 text-sm">
                    <span
                        class="flex flex-auto basis-28 items-center justify-center rounded-full border border-amber-200 bg-amber-50 px-3 py-1 font-medium text-amber-700">
                        Escopo nacional completo
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

                <article class="flex flex-col gap-5 rounded-[1.6rem] border border-slate-200 bg-white p-4 shadow-sm sm:p-5 xl:flex-row xl:items-start xl:gap-6">
                    <div class="flex-auto">
                        @if ($heroPrimary)
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
                                {{ $heroPrimary['label'] }}</p>
                            <p class="mt-3 text-5xl font-semibold tracking-tight text-slate-950">
                                {{ is_numeric($heroPrimary['value']) ? number_format((float) $heroPrimary['value'], 0, ',', '.') : $heroPrimary['value'] }}
                            </p>
                            <p class="mt-3 max-w-md text-sm leading-6 text-slate-600">
                                {{ $heroPrimary['description'] }}</p>
                        @endif

                        <div class="mt-5 flex flex-wrap gap-2">
                            @foreach ($dashboard['periodOptions'] as $option)
                                <a href="{{ route('app.director.dashboard', ['period' => $option['value']]) }}"
                                    class="flex flex-auto basis-20 items-center justify-center rounded-xl border px-4 py-2 text-sm font-semibold transition {{ $period->value === $option['value'] ? 'border-slate-950 bg-slate-950 text-white shadow-sm' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-400 hover:text-slate-950' }}">
                                    {{ $option['label'] }}
                                </a>
                            @endforeach
                        </div>

                    </div>

                    <form method="GET" action="{{ route('app.director.dashboard') }}"
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
                                    <a href="{{ route('app.director.dashboard', ['period' => $period->value]) }}"
                                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:text-slate-950">
                                        Limpar datas
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </article>
            </div>

            <aside class="grid gap-3 rounded-[1.7rem] border border-slate-200 bg-slate-50/80 p-4 sm:grid-cols-3 lg:grid-cols-1">
                <div>
                    <h2 class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Leitura da diretoria
                    </h2>
                    <p class="mt-1 text-sm leading-6 text-slate-600">A página foi organizada para mostrar primeiro o
                        que está bem, depois o que precisa de atenção.</p>
                </div>

                <article class="rounded-2xl border border-cyan-200 bg-white p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-cyan-700">1. Escala operacional
                    </p>
                    <p class="mt-2 text-lg font-semibold text-slate-950">Volume e adesão</p>
                    <p class="mt-1 text-sm leading-6 text-slate-600">Veja quantos treinamentos e inscrições existem
                        antes de analisar detalhes.</p>
                </article>

                <article class="rounded-2xl border border-emerald-200 bg-white p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">2. Expansão e
                        liderança</p>
                    <p class="mt-2 text-lg font-semibold text-slate-950">Capilaridade e cobertura</p>
                    <p class="mt-1 text-sm leading-6 text-slate-600">Esses números mostram onde o ministério está
                        alcançando mais pessoas e formando novas frentes.</p>
                </article>

                <article class="rounded-2xl border border-orange-200 bg-white p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-orange-700">3. Governança</p>
                    <p class="mt-2 text-lg font-semibold text-slate-950">Risco e sustentabilidade</p>
                    <p class="mt-1 text-sm leading-6 text-slate-600">No final da página ficam os alertas que ajudam
                        a decidir onde agir primeiro.</p>
                </article>
            </aside>
        </div>
    </section>

    <section class="my-6 grid gap-4 lg:grid-cols-2 xl:grid-cols-[1.2fr_1.2fr_0.8fr]">
        <article
            class="rounded-[1.7rem] border border-slate-200 bg-[linear-gradient(135deg,rgba(248,250,252,0.96),rgba(255,255,255,1))] p-5 shadow-[0_20px_60px_-42px_rgba(15,23,42,0.55)]">
            <div class="flex flex-col gap-2 border-b border-slate-200 pb-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                        Indicadores de eventos
                    </p>
                    <h2 class="mt-1 text-lg font-semibold text-slate-950">Status atuais da operação</h2>
                </div>
                <p class="text-sm leading-5 text-slate-600">Leitura rápida do andamento dos eventos nesta janela.</p>
            </div>

            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                @foreach ($eventStatusOverview as $status)
                    @php
                        $statusDescription = match ($status['key']) {
                            'planning' => 'Planejando',
                            'scheduled' => 'Agendado',
                            'completed' => 'Concluído',
                            'canceled' => 'Cancelado',
                            default => null,
                        };
                    @endphp

                    @if ($statusDescription)
                        <div class="rounded-2xl border px-4 py-3 shadow-sm {{ $status['tone'] }}">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-sm font-semibold tracking-[0.08em] uppercase">
                                    {{ $statusDescription }}
                                </span>
                                <span class="text-2xl font-semibold text-slate-950">
                                    {{ number_format($status['value'], 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </article>

        <article
            class="rounded-[1.7rem] border border-orange-200 bg-[linear-gradient(135deg,rgba(255,247,237,0.96),rgba(255,255,255,1))] p-5 shadow-[0_20px_60px_-42px_rgba(15,23,42,0.55)]">
            <div
                class="flex flex-col gap-2 border-b border-orange-200/80 pb-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-orange-700">
                        Estatísticas STP
                    </p>
                    <h2 class="mt-1 text-lg font-semibold text-slate-950">Resultados ministeriais</h2>
                </div>
                <p class="text-sm leading-5 text-slate-600">Resumo da jornada das conversas e acompanhamentos.</p>
            </div>

            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                @foreach ($stpOverview as $kpi)
                    @php
                        $stpTone = match ($kpi['key']) {
                            'gospel_explained' => [
                                'card' => 'border-orange-200 bg-orange-50/90',
                                'text' => 'text-orange-900',
                            ],
                            'people_reached' => [
                                'card' => 'border-amber-200 bg-amber-50/90',
                                'text' => 'text-amber-900',
                            ],
                            'decisions' => [
                                'card' => 'border-emerald-200 bg-emerald-50/90',
                                'text' => 'text-emerald-900',
                            ],
                            'scheduled_visits' => [
                                'card' => 'border-sky-200 bg-sky-50/90',
                                'text' => 'text-sky-900',
                            ],
                            default => [
                                'card' => 'border-slate-200 bg-slate-50',
                                'text' => 'text-slate-900',
                            ],
                        };
                    @endphp

                    <div class="rounded-2xl border px-4 py-3 shadow-sm {{ $stpTone['card'] }}">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-sm font-semibold tracking-[0.08em] uppercase {{ $stpTone['text'] }}">
                                {{ $kpi['label'] }}
                            </span>
                            <span class="text-2xl font-semibold {{ $stpTone['text'] }}">
                                {{ is_numeric($kpi['value']) ? number_format((float) $kpi['value'], 0, ',', '.') : $kpi['value'] }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </article>

        <div class="grid gap-4">
            @foreach ($balanceKpis as $kpi)
                @php
                    $kpiTone = match ($kpi['key']) {
                        'completed_trainings' => [
                            'card' =>
                                'border-emerald-200 bg-[linear-gradient(180deg,rgba(236,253,245,0.92),rgba(255,255,255,1))]',
                            'label' => 'text-emerald-700',
                        ],
                        default => [
                            'card' => 'border-slate-200 bg-white',
                            'label' => 'text-slate-600',
                        ],
                    };
                @endphp

                <article
                    class="rounded-[1.7rem] border p-5 shadow-[0_20px_60px_-42px_rgba(15,23,42,0.45)] {{ $kpiTone['card'] }}">
                    <div class="flex h-full flex-col">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] {{ $kpiTone['label'] }}">
                            {{ $kpi['label'] }}
                        </p>
                        <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">
                            {{ is_numeric($kpi['value']) ? number_format((float) $kpi['value'], 0, ',', '.') : $kpi['value'] }}
                        </p>
                        <p class="mt-3 text-sm leading-5 text-slate-600">
                            {{ $kpi['description'] }}
                        </p>
                    </div>
                </article>
            @endforeach

            <article
                class="rounded-[1.7rem] border border-violet-200 bg-[linear-gradient(180deg,rgba(245,243,255,0.95),rgba(255,255,255,1))] p-5 shadow-[0_20px_60px_-42px_rgba(15,23,42,0.45)]">
                <div class="flex flex-col gap-2 border-b border-violet-200/80 pb-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-violet-700">
                        Expansão da base
                    </p>
                    <h2 class="text-lg font-semibold text-slate-950">Pessoas, pastores e igrejas</h2>
                </div>

                <div class="mt-4 grid gap-3">
                    @foreach ($growthOverview as $kpi)
                        @php
                            $growthTone = match ($kpi['key']) {
                                'registrations' => 'border-sky-200 bg-sky-50/90 text-sky-900',
                                'pastors_trained' => 'border-emerald-200 bg-emerald-50/90 text-emerald-900',
                                'new_churches' => 'border-violet-200 bg-violet-50/90 text-violet-900',
                                default => 'border-slate-200 bg-slate-50 text-slate-900',
                            };
                        @endphp

                        <div class="rounded-2xl border px-4 py-3 shadow-sm {{ $growthTone }}">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-sm font-semibold tracking-[0.08em] uppercase">
                                    {{ $kpi['label'] }}
                                </span>
                                <span class="text-2xl font-semibold text-slate-950">
                                    {{ is_numeric($kpi['value']) ? number_format((float) $kpi['value'], 0, ',', '.') : $kpi['value'] }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </article>
        </div>
    </section>

    @foreach ($chartSections as $section)
        <section
            class="mb-6 rounded-[1.9rem] border border-slate-200 bg-white p-4 shadow-[0_20px_60px_-42px_rgba(15,23,42,0.7)] sm:p-6">
            <div class="border-b border-slate-200 pb-4">
                <h2 class="text-xl font-semibold text-slate-950">{{ $section['title'] }}</h2>
                <p class="mt-1 text-sm text-slate-600">{{ $section['description'] }}</p>
            </div>

            @if (($section['layout'] ?? 'grid') === 'swiper')
                <div class="relative mt-5">
                    <div class="swiper js-swiper-director-national SwiperDirectorNationalCharts px-1 sm:px-2">
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
            @else
                <div class="mt-5 grid gap-6 lg:grid-cols-2 2xl:grid-cols-4">
                    @foreach ($section['charts'] as $chart)
                        <x-dashboard.chart :chart="$chart" />
                    @endforeach
                </div>
            @endif
        </section>
    @endforeach

    <section class="mb-6 grid gap-6 xl:grid-cols-2">
        <article
            class="rounded-[1.9rem] border border-emerald-200 bg-[linear-gradient(180deg,rgba(236,253,245,0.86),rgba(255,255,255,1))] p-6 shadow-[0_20px_60px_-42px_rgba(15,23,42,0.7)]">
            <div class="flex items-center justify-between gap-3 border-b border-emerald-200/70 pb-4">
                <div>
                    <h2 class="text-xl font-semibold text-slate-950">Capilaridade e liderança</h2>
                    <p class="mt-1 text-sm text-slate-600">Mostra onde o ministério está chegando e quem está
                        ajudando esse crescimento.</p>
                </div>
                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                    Expansão
                </span>
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                @foreach ($expansionKpis as $kpi)
                    <article class="rounded-2xl border border-emerald-200 bg-white p-4 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">
                            {{ $kpi['label'] }}</p>
                        <p class="mt-3 text-3xl font-semibold text-slate-950">
                            {{ is_numeric($kpi['value']) ? number_format((float) $kpi['value'], 0, ',', '.') : $kpi['value'] }}
                        </p>
                        <p class="mt-2 text-sm leading-5 text-slate-600">{{ $kpi['description'] }}</p>
                    </article>
                @endforeach
            </div>
        </article>

        <article
            class="rounded-[1.9rem] border border-orange-200 bg-[linear-gradient(180deg,rgba(255,247,237,0.92),rgba(255,255,255,1))] p-6 shadow-[0_20px_60px_-42px_rgba(15,23,42,0.7)]">
            <div class="flex items-center justify-between gap-3 border-b border-orange-200/70 pb-4">
                <div>
                    <h2 class="text-xl font-semibold text-slate-950">Impacto ministerial</h2>
                    <p class="mt-1 text-sm text-slate-600">Mostra o resultado do trabalho de campo e do
                        acompanhamento das pessoas.</p>
                </div>
                <span class="rounded-full bg-orange-100 px-3 py-1 text-xs font-semibold text-orange-700">
                    STP
                </span>
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                @foreach ($impactKpis as $kpi)
                    <article class="rounded-2xl border border-orange-200 bg-white p-4 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-orange-700">
                            {{ $kpi['label'] }}</p>
                        <p class="mt-3 text-3xl font-semibold text-slate-950">
                            {{ is_numeric($kpi['value']) ? number_format((float) $kpi['value'], 0, ',', '.') : $kpi['value'] }}
                        </p>
                        <p class="mt-2 text-sm leading-5 text-slate-600">{{ $kpi['description'] }}</p>
                    </article>
                @endforeach
            </div>
        </article>
    </section>

    <section class="mb-40 sm:mb-32">
        <article
            class="rounded-[1.9rem] border border-slate-200 bg-white p-6 shadow-[0_20px_60px_-42px_rgba(15,23,42,0.7)]">
            <div class="mb-4 flex items-center justify-between gap-3 border-b border-slate-200 pb-4">
                <div>
                    <h2 class="text-xl font-semibold text-slate-950">Professores por curso de liderança</h2>
                    <p class="mt-1 text-sm text-slate-600">Veja com facilidade quem são os professores de cada
                        curso e onde cada um serve.</p>
                </div>

                <x-dashboard.help-tooltip title="Como ler: Professores por curso de liderança"
                    what="Mostra quais professores estao vinculados aos cursos de lideranca, se o vinculo esta ativo e quantos treinamentos cada um acumulou como titular e como auxiliar."
                    how="Use os filtros de curso para enxergar uma frente especifica. Na coluna de treinamentos, compare titular e auxiliar para entender carga e papel de cada professor. Os selos de curso mostram em quais frentes cada pessoa esta servindo." />
            </div>

            <div class="space-y-6"
                x-data="directorTeacherDirectory({
                    activeTeachers: @js($activeLeadershipTeachers->values()->all()),
                    inactiveTeachers: @js($inactiveLeadershipTeachers->values()->all()),
                    courseFilters: @js($leadershipCourseFilters->all()),
                })">
                <section>
                    <div class="mb-4 flex flex-wrap gap-2">
                        <button type="button" x-on:click="setCourseFilter(null)"
                            class="inline-flex items-center rounded-full border px-3 py-1.5 text-sm font-medium transition"
                            :class="selectedCourseId === null ? 'border-slate-900 bg-slate-900 text-white' :
                                'border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100'">
                            Todos os cursos
                        </button>

                        @foreach ($leadershipCourseFilters as $courseFilter)
                            <button type="button" x-on:click="setCourseFilter({{ $courseFilter['id'] }})"
                                class="inline-flex items-center rounded-full border px-3 py-1.5 text-sm font-medium transition duration-200"
                                :class="selectedCourseId === {{ $courseFilter['id'] }} ?
                                    'text-white shadow-sm' :
                                    'bg-white text-slate-800 shadow-sm'"
                                @if (!empty($courseFilter['color']))
                                    :style="selectedCourseId === {{ $courseFilter['id'] }} ?
                                        'border-color: {{ $courseFilter['color'] }}; background: linear-gradient(135deg, {{ $courseFilter['color'] }}, {{ $courseFilter['color'] }}DD); color: #ffffff;' :
                                        'border-color: {{ $courseFilter['color'] }}55; background: linear-gradient(135deg, {{ $courseFilter['color'] }}12, {{ $courseFilter['color'] }}20); color: #0f172a;'"
                                    x-on:mouseenter="if (selectedCourseId !== {{ $courseFilter['id'] }}) { $el.style.background = 'linear-gradient(135deg, {{ $courseFilter['color'] }}24, {{ $courseFilter['color'] }}38)'; $el.style.borderColor = '{{ $courseFilter['color'] }}99'; $el.style.color = '#020617'; }"
                                    x-on:mouseleave="if (selectedCourseId !== {{ $courseFilter['id'] }}) { $el.style.background = 'linear-gradient(135deg, {{ $courseFilter['color'] }}12, {{ $courseFilter['color'] }}20)'; $el.style.borderColor = '{{ $courseFilter['color'] }}55'; $el.style.color = '#0f172a'; }"
                                @endif>
                                {{ $courseFilter['type'] }}: {{ $courseFilter['name'] }}
                            </button>
                        @endforeach
                    </div>

                    <div class="-mx-4 overflow-x-auto px-4 sm:mx-0 sm:px-0">
                        <table class="w-full min-w-[780px] text-left text-sm whitespace-nowrap">
                            <thead>
                                <tr class="border-b border-slate-200 text-slate-500">
                                    <th class="px-3 py-2.5 font-semibold whitespace-nowrap">
                                        <button type="button" x-on:click="toggleSort('name')"
                                            class="inline-flex items-center gap-2 text-left whitespace-nowrap text-inherit">
                                            <span>Professor</span>
                                            <span x-text="sortIndicator('name')" class="text-xs"></span>
                                        </button>
                                    </th>
                                    <th class="px-3 py-2.5 font-semibold whitespace-nowrap">
                                        <button type="button" x-on:click="toggleSort('location')"
                                            class="inline-flex items-center gap-2 text-left whitespace-nowrap text-inherit">
                                            <span>Localização</span>
                                            <span x-text="sortIndicator('location')" class="text-xs"></span>
                                        </button>
                                    </th>
                                    <th class="px-3 py-2.5 font-semibold whitespace-nowrap">
                                        <button type="button" x-on:click="toggleSort('trainings')"
                                            class="inline-flex items-center gap-2 text-left whitespace-nowrap text-inherit">
                                            <span>Treinamentos</span>
                                            <span x-text="sortIndicator('trainings')" class="text-xs"></span>
                                        </button>
                                    </th>
                                    <th class="px-3 py-2.5 text-right font-semibold whitespace-nowrap">
                                        <button type="button" x-on:click="toggleSort('courses')"
                                            class="inline-flex items-center gap-2 whitespace-nowrap text-inherit">
                                            <span>Cursos</span>
                                            <span x-text="sortIndicator('courses')" class="text-xs"></span>
                                        </button>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(teacher, index) in activeTeachersView()" :key="`active-${teacher.name}-${index}`">
                                    <tr
                                        class="cursor-pointer border-b border-slate-200 transition hover:bg-slate-100/80 last:border-b-0"
                                        x-on:click="window.location = teacher.profile_url"
                                        :class="index % 2 === 0 ? 'bg-slate-50/90' : 'bg-white'">
                                        <td class="px-3 py-2.5 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-slate-500 text-xs font-semibold text-slate-50 ring-1 ring-black/10">
                                                    <img x-show="teacher.profile_photo_url" :src="teacher.profile_photo_url"
                                                        :alt="teacher.name" class="h-full w-full object-cover">
                                                    <span x-show="!teacher.profile_photo_url" x-text="teacher.initials"></span>
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="font-semibold text-slate-950" x-text="teacher.name?.toUpperCase()"></p>
                                                    <p class="text-sm text-slate-600 whitespace-nowrap"
                                                        x-text="teacher.church_name"></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-3 py-2.5 text-slate-600 whitespace-nowrap">
                                            <div>
                                                <p x-text="teacher.city"></p>
                                                <p class="text-xs text-slate-500" x-text="teacher.state"></p>
                                            </div>
                                        </td>
                                        <td class="px-3 py-2.5 text-slate-600 whitespace-nowrap">
                                            <div>
                                                <p x-text="`Titular: ${teacher.principal_trainings_count}`"></p>
                                                <p class="text-xs text-slate-500"
                                                    x-text="`Auxiliar: ${teacher.assistant_trainings_count}`"></p>
                                            </div>
                                        </td>
                                        <td class="px-3 py-2.5 whitespace-nowrap">
                                            <div class="flex justify-end pr-2">
                                                <template x-for="course in teacher.courses" :key="`active-course-${teacher.name}-${course.id}`">
                                                    <div class="ml-1 first:ml-0 sm:ml-1 md:-ml-1 md:first:ml-0 inline-flex h-9 min-w-9 items-center justify-center rounded-full border-2 px-2.5 text-[11px] font-bold tracking-[0.14em] text-white shadow-sm ring-2 ring-white"
                                                        :class="course.is_active ? '' : 'opacity-55 saturate-75'"
                                                        :title="course.is_active ? `${course.type} - ${course.name}` :
                                                            `${course.type} - ${course.name} | Vinculo inativo`"
                                                        :style="`background: linear-gradient(135deg, ${course.color}, ${course.color}CC); border-color: ${course.color};`">
                                                        <span x-text="course.initials"></span>
                                                    </div>
                                                </template>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="activeTeachersView().length === 0">
                                    <td colspan="4" class="px-3 py-4 text-slate-600">
                                        Nenhum professor ativo encontrado para o filtro selecionado.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section>
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-950">Professores inativos</h3>
                            <p class="mt-1 text-sm text-slate-600">Professores cadastrados em cursos de liderança, mas
                                marcados como inativos.</p>
                        </div>
                    </div>

                    <div class="-mx-4 mt-4 overflow-x-auto px-4 sm:mx-0 sm:px-0">
                        <table class="w-full min-w-[780px] text-left text-sm whitespace-nowrap">
                            <thead>
                                <tr class="border-b border-slate-200 text-slate-500">
                                    <th class="px-3 py-2.5 font-semibold whitespace-nowrap">
                                        <button type="button" x-on:click="toggleSort('name')"
                                            class="inline-flex items-center gap-2 text-left whitespace-nowrap text-inherit">
                                            <span>Professor</span>
                                            <span x-text="sortIndicator('name')" class="text-xs"></span>
                                        </button>
                                    </th>
                                    <th class="px-3 py-2.5 font-semibold whitespace-nowrap">
                                        <button type="button" x-on:click="toggleSort('location')"
                                            class="inline-flex items-center gap-2 text-left whitespace-nowrap text-inherit">
                                            <span>Localização</span>
                                            <span x-text="sortIndicator('location')" class="text-xs"></span>
                                        </button>
                                    </th>
                                    <th class="px-3 py-2.5 font-semibold whitespace-nowrap">
                                        <button type="button" x-on:click="toggleSort('trainings')"
                                            class="inline-flex items-center gap-2 text-left whitespace-nowrap text-inherit">
                                            <span>Treinamentos</span>
                                            <span x-text="sortIndicator('trainings')" class="text-xs"></span>
                                        </button>
                                    </th>
                                    <th class="px-3 py-2.5 text-right font-semibold whitespace-nowrap">
                                        <button type="button" x-on:click="toggleSort('courses')"
                                            class="inline-flex items-center gap-2 whitespace-nowrap text-inherit">
                                            <span>Cursos</span>
                                            <span x-text="sortIndicator('courses')" class="text-xs"></span>
                                        </button>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(teacher, index) in inactiveTeachersView()" :key="`inactive-${teacher.name}-${index}`">
                                    <tr
                                        class="cursor-pointer border-b border-slate-200 transition hover:bg-red-50/80 last:border-b-0"
                                        x-on:click="window.location = teacher.profile_url"
                                        :class="index % 2 === 0 ?
                                            'bg-[linear-gradient(90deg,rgba(254,242,242,0.95),rgba(255,255,255,1))]' :
                                            'bg-[linear-gradient(90deg,rgba(255,255,255,1),rgba(248,250,252,1))]'">
                                        <td class="px-3 py-2.5 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-slate-500 text-xs font-semibold text-slate-50 ring-1 ring-black/10">
                                                    <img x-show="teacher.profile_photo_url" :src="teacher.profile_photo_url"
                                                        :alt="teacher.name" class="h-full w-full object-cover">
                                                    <span x-show="!teacher.profile_photo_url" x-text="teacher.initials"></span>
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <p class="font-semibold text-slate-950" x-text="teacher.name?.toUpperCase()"></p>
                                                        <span
                                                            class="rounded-full border border-red-200 bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700">
                                                            Inativo
                                                        </span>
                                                    </div>
                                                    <p class="text-sm text-slate-600 whitespace-nowrap"
                                                        x-text="teacher.church_name"></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-3 py-2.5 text-slate-600 whitespace-nowrap">
                                            <div>
                                                <p x-text="teacher.city"></p>
                                                <p class="text-xs text-slate-500" x-text="teacher.state"></p>
                                            </div>
                                        </td>
                                        <td class="px-3 py-2.5 text-slate-600 whitespace-nowrap">
                                            <div>
                                                <p x-text="`Titular: ${teacher.principal_trainings_count}`"></p>
                                                <p class="text-xs text-slate-500"
                                                    x-text="`Auxiliar: ${teacher.assistant_trainings_count}`"></p>
                                            </div>
                                        </td>
                                        <td class="px-3 py-2.5 whitespace-nowrap">
                                            <div class="flex justify-end pr-2">
                                                <template x-for="course in teacher.courses" :key="`inactive-course-${teacher.name}-${course.id}`">
                                                    <div class="ml-1 first:ml-0 sm:ml-1 md:-ml-1 md:first:ml-0 inline-flex h-9 min-w-9 items-center justify-center rounded-full border-2 px-2.5 text-[11px] font-bold tracking-[0.14em] text-white shadow-sm ring-2 ring-white"
                                                        :class="course.is_active ? '' : 'opacity-55 saturate-75'"
                                                        :title="course.is_active ? `${course.type} - ${course.name}` :
                                                            `${course.type} - ${course.name} | Vinculo inativo`"
                                                        :style="`background: linear-gradient(135deg, ${course.color}, ${course.color}CC); border-color: ${course.color};`">
                                                        <span x-text="course.initials"></span>
                                                    </div>
                                                </template>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="inactiveTeachersView().length === 0">
                                    <td colspan="4" class="px-3 py-4 text-slate-600">
                                        Nenhum professor inativo encontrado para o filtro selecionado.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </article>
    </section>

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
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-300">Governança</p>
                        <p class="text-xs text-slate-400">Alertas prioritários para ação da diretoria.</p>
                    </div>
                </div>

                <span
                    class="inline-flex size-7 shrink-0 items-center justify-center rounded-full border border-white/10 bg-white/10 text-slate-200">
                    <flux:icon.chevron-down class="size-3.5 transition duration-200"
                        x-bind:class="open ? '' : 'rotate-180'" />
                </span>
            </button>

            <div x-cloak x-show="open" x-collapse
                class="director-governance-scroll max-h-[65vh] overflow-y-auto bg-sky-950 px-4 py-4 sm:max-h-[70vh]">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold">Governança e alertas</h2>
                    <p class="mt-1 text-sm text-slate-300">Aqui ficam os alertas mais importantes para saber onde agir
                        primeiro.</p>
                </div>

                <div class="space-y-5">
                    @foreach ($governanceSections as $section)
                        <section>
                            <div>
                                <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-300">
                                    {{ $section['title'] }}</h3>
                                <p class="mt-1 text-sm text-slate-400">{{ $section['description'] }}</p>
                            </div>

                            <div class="mt-3 grid gap-2">
                                @forelse ($section['items'] as $item)
                                    @if ($section['title'] === 'Risco operacional' && isset($item['route']))
                                        <a href="{{ $item['route'] }}"
                                            class="block rounded-xl border px-4 py-3 text-sm transition hover:bg-white/90 {{ $section['tone'] }}">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <p class="font-semibold text-current">
                                                        {{ $item['label'] }}
                                                    </p>
                                                    <p class="mt-1 text-sm text-current/80">
                                                        {{ $item['context'] }}
                                                    </p>
                                                </div>
                                                <span
                                                    class="shrink-0 rounded-full bg-white/70 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-800">
                                                    Abrir
                                                </span>
                                            </div>

                                            <div class="mt-3 grid gap-2 text-xs sm:grid-cols-2">
                                                <div class="rounded-lg bg-white/55 px-3 py-2 text-slate-800">
                                                    <span
                                                        class="block font-semibold uppercase tracking-[0.12em] text-slate-500">
                                                        Professor titular
                                                    </span>
                                                    <span class="mt-1 block font-medium">
                                                        {{ $item['teacher_name'] }}
                                                    </span>
                                                </div>
                                                <div class="rounded-lg bg-white/55 px-3 py-2 text-slate-800">
                                                    <span
                                                        class="block font-semibold uppercase tracking-[0.12em] text-slate-500">
                                                        Data
                                                    </span>
                                                    <span class="mt-1 block font-medium">
                                                        {{ $item['event_date'] }}
                                                    </span>
                                                </div>
                                            </div>
                                        </a>
                                    @elseif (isset($item['route']))
                                        <a href="{{ $item['route'] }}"
                                            class="block rounded-xl border px-4 py-3 text-sm transition hover:bg-white/90 {{ $section['tone'] }}">
                                            {{ $section['render']($item) }}
                                        </a>
                                    @else
                                        <article class="rounded-xl border px-4 py-3 text-sm {{ $section['tone'] }}">
                                            {{ $section['render']($item) }}
                                        </article>
                                    @endif
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
</x-layouts.app>
