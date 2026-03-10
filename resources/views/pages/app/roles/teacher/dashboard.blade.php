<x-layouts.app :title="__('Dashboard do Professor')">
    @php
        $period = $dashboard['period'];
    @endphp

    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-[2rem] bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.16),_transparent_32%),linear-gradient(180deg,_rgba(255,255,255,0.92),_rgba(241,245,249,0.96))] p-1">
        <section class="overflow-hidden rounded-[1.75rem] border border-sky-100 bg-white/95 shadow-sm">
            <div class="grid gap-6 px-6 py-6 lg:grid-cols-[1.6fr_0.9fr] lg:px-8">
                <div class="space-y-4">
                    <div class="inline-flex items-center gap-2 rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-sky-800">
                        Dashboard do Professor
                    </div>
                    <div class="space-y-2">
                        <h1 class="text-3xl font-semibold tracking-tight text-slate-950">
                            Operação do campo, STP/OJT e discipulado paralelo em uma única visão.
                        </h1>
                        <p class="max-w-3xl text-sm leading-6 text-slate-600">
                            Escopo filtrado apenas pelos treinamentos em que você atua como professor titular ou auxiliar, com prioridade real para prática, acompanhamento e continuidade ministerial.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 text-sm text-slate-600">
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 font-medium text-slate-700">
                            Período atual: {{ $dashboard['periodLabel'] }}
                        </span>
                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 font-medium text-emerald-700">
                            Janela: {{ $dashboard['rangeLabel'] }}
                        </span>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($dashboard['periodOptions'] as $option)
                            <a
                                href="{{ route('app.teacher.dashboard', ['period' => $option['value']]) }}"
                                class="inline-flex min-w-28 items-center justify-center rounded-xl border px-4 py-2 text-sm font-semibold transition {{ $period->value === $option['value'] ? 'border-sky-900 bg-sky-950 text-white shadow-sm' : 'border-slate-200 bg-white text-slate-600 hover:border-sky-300 hover:text-sky-900' }}"
                            >
                                {{ $option['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="grid gap-3 rounded-[1.5rem] border border-slate-200 bg-slate-50/80 p-4">
                    <div>
                        <h2 class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Ações rápidas</h2>
                        <p class="mt-1 text-sm text-slate-600">Atalhos reaproveitados para a rotina operacional imediata.</p>
                    </div>
                    @foreach ($dashboard['quickActions'] as $action)
                        <a
                            href="{{ $action['href'] }}"
                            class="inline-flex items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 transition hover:border-sky-400 hover:text-sky-900"
                        >
                            <span>{{ $action['label'] }}</span>
                            <span class="text-sky-500">●</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($dashboard['kpis'] as $kpi)
                <article class="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{{ $kpi['label'] }}</p>
                    <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">{{ number_format($kpi['value'], 0, ',', '.') }}</p>
                    <p class="mt-2 text-sm leading-5 text-slate-600">{{ $kpi['description'] }}</p>
                </article>
            @endforeach
        </section>

        <div class="grid gap-6 xl:grid-cols-[1.3fr_0.9fr]">
            <section class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-3 border-b border-slate-200 pb-4">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-950">Impacto evangelístico</h2>
                        <p class="mt-1 text-sm text-slate-600">Consolidação de STP/OJT com foco em alcance, decisão e retorno.</p>
                    </div>
                    <span class="rounded-full bg-orange-50 px-3 py-1 text-xs font-semibold text-orange-700">
                        Ênfase STP/OJT
                    </span>
                </div>
                <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($dashboard['evangelisticImpact'] as $card)
                        <article class="rounded-2xl border border-orange-100 bg-orange-50/50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-orange-700">{{ $card['label'] }}</p>
                            <p class="mt-3 text-3xl font-semibold text-slate-950">{{ number_format($card['value'], 0, ',', '.') }}</p>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-3 border-b border-slate-200 pb-4">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-950">Discipulado paralelo</h2>
                        <p class="mt-1 text-sm text-slate-600">Continuidade ministerial após a prática, com acompanhamento e encaminhamento local.</p>
                    </div>
                    <span class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700">
                        Trilha paralela
                    </span>
                </div>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    @foreach ($dashboard['discipleship']['cards'] as $card)
                        <article class="rounded-2xl border border-cyan-100 bg-cyan-50/50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-cyan-800">{{ $card['label'] }}</p>
                            <p class="mt-3 text-3xl font-semibold text-slate-950">{{ number_format($card['value'], 0, ',', '.') }}</p>
                        </article>
                    @endforeach
                </div>
            </section>
        </div>

        <section class="grid gap-6 xl:grid-cols-2">
            @foreach ($dashboard['charts'] as $chart)
                <x-dashboard.chart :chart="$chart" />
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <article class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between gap-3 border-b border-slate-200 pb-4">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-950">Próximos treinamentos</h2>
                        <p class="mt-1 text-sm text-slate-600">O que exige ação imediata na agenda.</p>
                    </div>
                </div>
                <div class="grid gap-3">
                    @forelse ($dashboard['operational']['nextTrainings'] as $item)
                        <article class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <h3 class="font-semibold text-slate-950">{{ $item['label'] }}</h3>
                                    <p class="mt-1 text-sm text-slate-600">{{ $item['first_date'] ?? 'Data a confirmar' }}</p>
                                </div>
                                <a href="{{ $item['route'] }}" class="text-sm font-semibold text-sky-700 hover:text-sky-900">Abrir</a>
                            </div>
                        </article>
                    @empty
                        <p class="text-sm text-slate-600">Nenhum treinamento futuro dentro da janela selecionada.</p>
                    @endforelse
                </div>
            </article>

            <article class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between gap-3 border-b border-slate-200 pb-4">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-950">Treinamentos com pendências</h2>
                        <p class="mt-1 text-sm text-slate-600">Reaproveita a mesma inteligência de programação e validação já usada nas páginas.</p>
                    </div>
                </div>
                <div class="grid gap-3">
                    @forelse ($dashboard['operational']['pendingTrainings'] as $item)
                        <article class="rounded-2xl border border-amber-200 bg-amber-50/70 p-4">
                            <h3 class="font-semibold text-slate-950">{{ $item['label'] }}</h3>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @if ($item['has_schedule_issue'])
                                    <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-200">Programação pendente</span>
                                @endif
                                @if ($item['has_registration_issue'])
                                    <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-200">Validação/igreja pendente</span>
                                @endif
                            </div>
                            <div class="mt-3 flex flex-wrap gap-2 text-sm font-semibold">
                                <a href="{{ $item['schedule_route'] }}" class="text-sky-700 hover:text-sky-900">Programação</a>
                                <a href="{{ $item['registrations_route'] }}" class="text-sky-700 hover:text-sky-900">Inscrições</a>
                                <a href="{{ $item['route'] }}" class="text-sky-700 hover:text-sky-900">Treinamento</a>
                            </div>
                        </article>
                    @empty
                        <p class="text-sm text-slate-600">Sem pendências operacionais no momento.</p>
                    @endforelse
                </div>
            </article>

            <article class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 border-b border-slate-200 pb-4">
                    <h2 class="text-xl font-semibold text-slate-950">Treinamentos sem mentores suficientes</h2>
                    <p class="mt-1 text-sm text-slate-600">Relação mínima operacional considerada: 1 mentor para cada 4 alunos.</p>
                </div>
                <div class="grid gap-3">
                    @forelse ($dashboard['operational']['mentorShortageTrainings'] as $item)
                        <article class="rounded-2xl border border-rose-200 bg-rose-50/70 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="font-semibold text-slate-950">{{ $item['label'] }}</h3>
                                    <p class="mt-1 text-sm text-slate-600">{{ $item['mentor_shortage_context'] }}</p>
                                </div>
                                <a href="{{ $item['route'] }}" class="text-sm font-semibold text-sky-700 hover:text-sky-900">Abrir</a>
                            </div>
                        </article>
                    @empty
                        <p class="text-sm text-slate-600">Nenhum treinamento está abaixo da cobertura mínima de mentoria.</p>
                    @endforelse
                </div>
            </article>

            <article class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 border-b border-slate-200 pb-4">
                    <h2 class="text-xl font-semibold text-slate-950">STP/OJT com baixa conclusão</h2>
                    <p class="mt-1 text-sm text-slate-600">Treinamentos cuja conclusão prática está abaixo de 60% do planejado.</p>
                </div>
                <div class="grid gap-3">
                    @forelse ($dashboard['operational']['lowStpCompletionTrainings'] as $item)
                        <article class="rounded-2xl border border-orange-200 bg-orange-50/70 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="font-semibold text-slate-950">{{ $item['label'] }}</h3>
                                    <p class="mt-1 text-sm text-slate-600">{{ $item['stp_completion_context'] }}</p>
                                </div>
                                <a href="{{ $item['statistics_route'] }}" class="text-sm font-semibold text-sky-700 hover:text-sky-900">Ver quadro</a>
                            </div>
                        </article>
                    @empty
                        <p class="text-sm text-slate-600">As sessões STP/OJT estão com conclusão saudável na janela atual.</p>
                    @endforelse
                </div>
            </article>

            <article class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm xl:col-span-2">
                <div class="mb-4 border-b border-slate-200 pb-4">
                    <h2 class="text-xl font-semibold text-slate-950">Discipulado sem continuidade</h2>
                    <p class="mt-1 text-sm text-slate-600">Casos em acompanhamento sem próximo passo suficiente ou com follow-up ainda pendente.</p>
                </div>
                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                    @forelse ($dashboard['operational']['discipleshipWithoutContinuityTrainings'] as $item)
                        <article class="rounded-2xl border border-cyan-200 bg-cyan-50/70 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="font-semibold text-slate-950">{{ $item['label'] }}</h3>
                                    <p class="mt-1 text-sm text-slate-600">{{ $item['discipleship_context'] }}</p>
                                </div>
                                <a href="{{ $item['stp_route'] }}" class="text-sm font-semibold text-sky-700 hover:text-sky-900">Ir para STP</a>
                            </div>
                        </article>
                    @empty
                        <p class="text-sm text-slate-600">Nenhum treinamento com indício de ruptura na continuidade do discipulado.</p>
                    @endforelse
                </div>
            </article>
        </section>
    </div>
</x-layouts.app>
