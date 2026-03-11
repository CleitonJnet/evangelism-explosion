<x-layouts.app :title="__('Raio-X Nacional')">
    @php
        $period = $dashboard['period'];
        $kpis = collect($dashboard['kpis']);
        $charts = collect($dashboard['charts']);
        $filters = $dashboard['filters'] ?? ['startDate' => null, 'endDate' => null, 'usingCustomRange' => false];

        $executiveKpis = $kpis
            ->whereIn('key', ['trainings', 'registrations', 'paid_students', 'payment_rate'])
            ->values();

        $expansionKpis = $kpis
            ->whereIn('key', ['churches_reached', 'new_churches', 'active_teachers', 'pastors_trained'])
            ->values();

        $impactKpis = $kpis
            ->whereIn('key', ['gospel_explained', 'people_reached', 'decisions', 'discipleship'])
            ->values();

        $balanceKpis = $kpis
            ->whereIn('key', ['future_trainings', 'completed_trainings', 'scheduled_visits', 'ee_balance'])
            ->values();

        $heroPrimary = $executiveKpis->first();
        $heroSecondary = $executiveKpis->slice(1)->values();

        $chartSections = [
            [
                'title' => 'Ritmo nacional',
                'description' => 'Evolução da operação ao longo da janela selecionada.',
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

    <section
        class="overflow-hidden rounded-4xl mb-6 border border-slate-200 bg-[linear-gradient(135deg,rgba(255,255,255,0.98),rgba(241,245,249,0.95))] shadow-[0_24px_80px_-48px_rgba(15,23,42,0.45)]">
        <div class="grid gap-6 px-6 py-6 lg:grid-cols-[1.4fr_0.9fr] lg:px-8 lg:py-8">
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
                        class="flex flex-auto basis-28 items-center justify-center rounded-full border border-slate-200 bg-white px-3 py-1 font-medium text-slate-700">
                        Período atual: {{ $dashboard['periodLabel'] }}
                    </span>
                    <span
                        class="flex flex-auto basis-28 items-center justify-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 font-medium text-emerald-700">
                        Janela: {{ $dashboard['rangeLabel'] }}
                    </span>
                    <span
                        class="flex flex-auto basis-28 items-center justify-center rounded-full border border-amber-200 bg-amber-50 px-3 py-1 font-medium text-amber-700">
                        Escopo nacional completo
                    </span>
                </div>

                <article class="rounded-[1.6rem] border border-slate-200 bg-white p-5 shadow-sm flex gap-6">
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
                        class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4 flex-auto basis-80">
                        <div class="flex flex-col gap-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">Filtrar por datas exatas</p>
                                <p class="mt-1 text-sm text-slate-600">Use este campo quando quiser consultar um
                                    começo e um fim específicos.</p>
                            </div>

                            <div class="grid gap-3 md:grid-cols-2">
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

            <aside class="grid gap-3 rounded-[1.7rem] border border-slate-200 bg-slate-50/80 p-4">
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

    <section class="my-6 flex flex-wrap gap-3">
        @foreach ($balanceKpis as $kpi)
            <article class="rounded-2xl border border-slate-300 bg-white p-4 shadow-sm flex-auto basis-20">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 truncate">
                    {{ $kpi['label'] }}</p>
                <p class="mt-2 text-2xl font-semibold text-slate-950">
                    {{ is_numeric($kpi['value']) ? number_format((float) $kpi['value'], 0, ',', '.') : $kpi['value'] }}
                </p>
                <p class="mt-2 text-sm leading-5 text-slate-600">{{ $kpi['description'] }}</p>
            </article>
        @endforeach
    </section>

    @foreach ($chartSections as $section)
        <section
            class="rounded-[1.9rem] mb-6 border border-slate-200 bg-white p-6 shadow-[0_20px_60px_-42px_rgba(15,23,42,0.7)]">
            <div class="border-b border-slate-200 pb-4">
                <h2 class="text-xl font-semibold text-slate-950">{{ $section['title'] }}</h2>
                <p class="mt-1 text-sm text-slate-600">{{ $section['description'] }}</p>
            </div>

            <div class="mt-5 grid gap-6 lg:grid-cols-2 2xl:grid-cols-4">
                @foreach ($section['charts'] as $chart)
                    <x-dashboard.chart :chart="$chart" />
                @endforeach
            </div>
        </section>
    @endforeach

    <section class="mb-6 grid gap-6 xl:grid-cols-[1.35fr_0.95fr]">
        <article
            class="rounded-[1.9rem] border border-slate-200 bg-white p-6 shadow-[0_20px_60px_-42px_rgba(15,23,42,0.7)]">
            <div class="flex items-start justify-between gap-3 border-b border-slate-200 pb-4">
                <div>
                    <h2 class="text-xl font-semibold text-slate-950">Panorama executivo</h2>
                    <p class="mt-1 text-sm text-slate-600">Resumo direto dos números principais deste período.</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                    Camada principal
                </span>
            </div>

            <div class="mt-5 grid gap-4 md:grid-cols-3">
                <article class="rounded-[1.6rem] border border-slate-900 bg-slate-950 p-5 text-white md:col-span-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-300">Foco da diretoria
                    </p>
                    <p class="mt-3 text-2xl font-semibold tracking-tight">O objetivo é acompanhar crescimento,
                        resultado do campo e estabilidade financeira sem complicação.</p>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-300">Leia primeiro os totais, depois o
                        alcance e, por fim, os alertas que precisam de ação.</p>
                </article>

                <article class="rounded-[1.6rem] border border-emerald-200 bg-emerald-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">Leitura imediata
                    </p>
                    <p class="mt-3 text-3xl font-semibold text-slate-950">{{ $dashboard['periodLabel'] }}</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Período selecionado para acompanhar os
                        resultados com precisão.</p>
                </article>
            </div>

            <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @foreach ($executiveKpis as $kpi)
                    <article
                        class="rounded-2xl border border-slate-300 bg-white p-4 shadow-sm transition hover:border-slate-400">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                            {{ $kpi['label'] }}</p>
                        <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">
                            {{ is_numeric($kpi['value']) ? number_format((float) $kpi['value'], 0, ',', '.') : $kpi['value'] }}
                        </p>
                        <p class="mt-2 text-sm leading-5 text-slate-600">{{ $kpi['description'] }}</p>
                    </article>
                @endforeach
            </div>
        </article>

        <article
            class="rounded-[1.9rem] border border-slate-200 bg-[linear-gradient(180deg,rgba(248,250,252,1),rgba(241,245,249,0.92))] p-6 shadow-[0_20px_60px_-42px_rgba(15,23,42,0.7)]">
            <div class="border-b border-slate-200 pb-4">
                <h2 class="text-xl font-semibold text-slate-950">Equilíbrio da operação</h2>
                <p class="mt-1 text-sm text-slate-600">Indicadores para entender se o ministério está avançando com
                    equilíbrio.</p>
            </div>

            <div class="mt-5 grid gap-3">
                @foreach ($balanceKpis as $kpi)
                    <article class="rounded-2xl border border-slate-300 bg-white p-4 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                            {{ $kpi['label'] }}</p>
                        <p class="mt-2 text-2xl font-semibold text-slate-950">
                            {{ is_numeric($kpi['value']) ? number_format((float) $kpi['value'], 0, ',', '.') : $kpi['value'] }}
                        </p>
                        <p class="mt-2 text-sm leading-5 text-slate-600">{{ $kpi['description'] }}</p>
                    </article>
                @endforeach
            </div>
        </article>
    </section>

    <section class="grid gap-6 xl:grid-cols-2 mb-6">
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

    <section class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
        <article
            class="rounded-[1.9rem] border border-slate-200 bg-white p-6 shadow-[0_20px_60px_-42px_rgba(15,23,42,0.7)]">
            <div class="mb-4 flex items-center justify-between gap-3 border-b border-slate-200 pb-4">
                <div>
                    <h2 class="text-xl font-semibold text-slate-950">Professores por curso de liderança</h2>
                    <p class="mt-1 text-sm text-slate-600">Veja com facilidade quem são os professores de cada
                        curso e onde cada um serve.</p>
                </div>
            </div>

            <div class="space-y-4">
                @forelse ($dashboard['leadershipTeachers'] as $row)
                    <article class="rounded-2xl border border-slate-300 bg-white p-4 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h3 class="font-semibold text-slate-950">{{ $row['course_name'] }}</h3>
                                <p class="mt-1 text-sm text-slate-600">{{ $row['ministry_name'] }}</p>
                            </div>
                            <span
                                class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">
                                {{ count($row['teachers']) }} professor(es)
                            </span>
                        </div>

                        <div class="mt-4 grid gap-3 md:grid-cols-2">
                            @forelse ($row['teachers'] as $teacher)
                                <article class="rounded-2xl border border-slate-300 bg-slate-50/60 p-4">
                                    <div class="flex items-center gap-3">
                                        <flux:avatar class="bg-slate-500 text-slate-50 after:inset-ring-black/10"
                                            :name="$teacher['name']" :src="$teacher['profile_photo_url']"
                                            :initials="$teacher['initials']" />
                                        <div class="min-w-0">
                                            <p class="truncate font-semibold text-slate-950">
                                                {{ $teacher['name'] }}</p>
                                            <p class="text-sm text-slate-600">{{ $teacher['church_name'] }}</p>
                                        </div>
                                    </div>

                                    <div
                                        class="mt-4 flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2">
                                        <span class="text-sm font-medium text-slate-600">Situação no curso</span>
                                        <span
                                            class="rounded-full px-3 py-1 text-xs font-semibold {{ $teacher['status'] === 'Ativo' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }}">
                                            {{ $teacher['status'] }}
                                        </span>
                                    </div>
                                </article>
                            @empty
                                <p class="text-sm text-slate-600">Nenhum professor vinculado a este curso.</p>
                            @endforelse
                        </div>
                    </article>
                @empty
                    <p class="text-sm text-slate-600">Nenhum curso de liderança encontrado na base atual.</p>
                @endforelse
            </div>
        </article>

        <article
            class="rounded-[1.9rem] border border-slate-900 bg-slate-950 p-6 text-white shadow-[0_20px_60px_-42px_rgba(15,23,42,0.9)]">
            <div class="mb-4 border-b border-white/10 pb-4">
                <h2 class="text-xl font-semibold">Governança e alertas</h2>
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
                                <article class="rounded-xl border px-4 py-3 text-sm {{ $section['tone'] }}">
                                    {{ $section['render']($item) }}
                                </article>
                            @empty
                                <p class="text-sm text-slate-400">{{ $section['empty'] }}</p>
                            @endforelse
                        </div>
                    </section>
                @endforeach
            </div>
        </article>
    </section>
</x-layouts.app>
