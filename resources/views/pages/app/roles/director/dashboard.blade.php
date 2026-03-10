<x-layouts.app :title="__('Raio-X Nacional')">
    @php
        $period = $dashboard['period'];
    @endphp

    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-[2rem] bg-[radial-gradient(circle_at_top_left,_rgba(14,116,144,0.12),_transparent_30%),radial-gradient(circle_at_top_right,_rgba(245,158,11,0.12),_transparent_28%),linear-gradient(180deg,_rgba(255,255,255,0.98),_rgba(241,245,249,0.96))] p-1">
        <section class="overflow-hidden rounded-[1.8rem] border border-slate-200 bg-white/95 shadow-sm">
            <div class="grid gap-6 px-6 py-6 lg:grid-cols-[1.55fr_0.85fr] lg:px-8">
                <div class="space-y-4">
                    <div class="inline-flex items-center gap-2 rounded-full bg-slate-950 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-white">
                        Raio-X Nacional
                    </div>
                    <div class="space-y-2">
                        <h1 class="text-3xl font-semibold tracking-tight text-slate-950">
                            Visão nacional, operacional e ministerial consolidada do EE Brasil.
                        </h1>
                        <p class="max-w-3xl text-sm leading-6 text-slate-600">
                            Painel estratégico do diretor com consolidação de treinamentos, STP/OJT, discipulado, igrejas, professores, expansão e saúde financeira do ministério.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 text-sm text-slate-600">
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 font-medium text-slate-700">
                            Período atual: {{ $dashboard['periodLabel'] }}
                        </span>
                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 font-medium text-emerald-700">
                            Janela: {{ $dashboard['rangeLabel'] }}
                        </span>
                        <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 font-medium text-amber-700">
                            Escopo nacional completo
                        </span>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($dashboard['periodOptions'] as $option)
                            <a
                                href="{{ route('app.director.dashboard', ['period' => $option['value']]) }}"
                                class="inline-flex min-w-28 items-center justify-center rounded-xl border px-4 py-2 text-sm font-semibold transition {{ $period->value === $option['value'] ? 'border-slate-950 bg-slate-950 text-white shadow-sm' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-400 hover:text-slate-950' }}"
                            >
                                {{ $option['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="grid gap-3 rounded-[1.5rem] border border-slate-200 bg-slate-50/90 p-4">
                    <div>
                        <h2 class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Leituras estratégicas</h2>
                        <p class="mt-1 text-sm text-slate-600">O painel combina saúde nacional, expansão, prática e risco operacional.</p>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <article class="rounded-2xl border border-slate-200 bg-white p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Foco ministerial</p>
                            <p class="mt-2 text-lg font-semibold text-slate-950">STP/OJT + discipulado</p>
                            <p class="mt-1 text-sm text-slate-600">Da prática evangelística ao acompanhamento intencional.</p>
                        </article>
                        <article class="rounded-2xl border border-slate-200 bg-white p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Classificação de cursos</p>
                            <p class="mt-2 text-lg font-semibold text-slate-950">Liderança reaproveitada</p>
                            <p class="mt-1 text-sm text-slate-600">Baseada em `execution = 0`, agora exposta por helper explícito.</p>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($dashboard['kpis'] as $kpi)
                <article class="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{{ $kpi['label'] }}</p>
                    <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">{{ is_numeric($kpi['value']) ? number_format((float) $kpi['value'], 0, ',', '.') : $kpi['value'] }}</p>
                    <p class="mt-2 text-sm leading-5 text-slate-600">{{ $kpi['description'] }}</p>
                </article>
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            @foreach ($dashboard['charts'] as $chart)
                <x-dashboard.chart :chart="$chart" />
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
            <article class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between gap-3 border-b border-slate-200 pb-4">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-950">Professores por curso de liderança</h2>
                        <p class="mt-1 text-sm text-slate-600">Lista obrigatória baseada na classificação formal de liderança do domínio.</p>
                    </div>
                </div>
                <div class="space-y-4">
                    @forelse ($dashboard['leadershipTeachers'] as $row)
                        <article class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <h3 class="font-semibold text-slate-950">{{ $row['course_name'] }}</h3>
                                    <p class="mt-1 text-sm text-slate-600">{{ $row['ministry_name'] }}</p>
                                </div>
                                <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">
                                    {{ count($row['teachers']) }} professor(es)
                                </span>
                            </div>
                            <div class="mt-4 overflow-x-auto">
                                <table class="min-w-full text-left text-sm">
                                    <thead>
                                        <tr class="text-slate-500">
                                            <th class="px-3 py-2 font-semibold">Professor</th>
                                            <th class="px-3 py-2 font-semibold">Status</th>
                                            <th class="px-3 py-2 font-semibold">Igreja</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($row['teachers'] as $teacher)
                                            <tr class="border-t border-slate-200">
                                                <td class="px-3 py-2 font-medium text-slate-900">{{ $teacher['name'] }}</td>
                                                <td class="px-3 py-2 text-slate-700">{{ $teacher['status'] }}</td>
                                                <td class="px-3 py-2 text-slate-700">{{ $teacher['church_name'] }}</td>
                                            </tr>
                                        @empty
                                            <tr class="border-t border-slate-200">
                                                <td colspan="3" class="px-3 py-3 text-slate-600">Nenhum professor vinculado a este curso.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </article>
                    @empty
                        <p class="text-sm text-slate-600">Nenhum curso de liderança encontrado na base atual.</p>
                    @endforelse
                </div>
            </article>

            <article class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 border-b border-slate-200 pb-4">
                    <h2 class="text-xl font-semibold text-slate-950">Alertas nacionais</h2>
                    <p class="mt-1 text-sm text-slate-600">Leituras rápidas dos gargalos mais relevantes do período.</p>
                </div>

                <div class="space-y-5">
                    <section>
                        <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Regiões pouco atendidas</h3>
                        <div class="mt-3 grid gap-2">
                            @forelse ($dashboard['alerts']['regions'] as $item)
                                <article class="rounded-xl border border-amber-200 bg-amber-50/70 px-4 py-3 text-sm text-amber-900">
                                    <span class="font-semibold">{{ $item['label'] }}</span> · {{ $item['context'] }}
                                </article>
                            @empty
                                <p class="text-sm text-slate-600">Sem regiões críticas identificadas na janela atual.</p>
                            @endforelse
                        </div>
                    </section>

                    <section>
                        <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Treinamentos com risco operacional</h3>
                        <div class="mt-3 grid gap-2">
                            @forelse ($dashboard['alerts']['operationalRisks'] as $item)
                                <article class="rounded-xl border border-rose-200 bg-rose-50/70 px-4 py-3 text-sm text-rose-900">
                                    <span class="font-semibold">{{ $item['label'] }}</span> · {{ $item['context'] }}
                                </article>
                            @empty
                                <p class="text-sm text-slate-600">Sem treinamentos críticos no momento.</p>
                            @endforelse
                        </div>
                    </section>

                    <section>
                        <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Gargalos financeiros</h3>
                        <div class="mt-3 grid gap-2">
                            @forelse ($dashboard['alerts']['financialBottlenecks'] as $item)
                                <article class="rounded-xl border border-orange-200 bg-orange-50/70 px-4 py-3 text-sm text-orange-900">
                                    <span class="font-semibold">{{ $item['label'] }}</span> · {{ $item['payment_rate'] }}% de pagamento
                                </article>
                            @empty
                                <p class="text-sm text-slate-600">Nenhum gargalo financeiro abaixo do limiar configurado.</p>
                            @endforelse
                        </div>
                    </section>

                    <section>
                        <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Professores sobrecarregados</h3>
                        <div class="mt-3 grid gap-2">
                            @forelse ($dashboard['alerts']['overloadedTeachers'] as $item)
                                <article class="rounded-xl border border-cyan-200 bg-cyan-50/70 px-4 py-3 text-sm text-cyan-900">
                                    <span class="font-semibold">{{ $item['label'] }}</span> · {{ $item['value'] }} treinamentos como titular
                                </article>
                            @empty
                                <p class="text-sm text-slate-600">Sem concentração crítica de carga entre professores titulares.</p>
                            @endforelse
                        </div>
                    </section>

                    <section>
                        <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Cursos com baixa recorrência</h3>
                        <div class="mt-3 grid gap-2">
                            @forelse ($dashboard['alerts']['lowRecurrenceCourses'] as $item)
                                <article class="rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-3 text-sm text-slate-800">
                                    <span class="font-semibold">{{ $item['label'] }}</span> · {{ $item['context'] }}
                                </article>
                            @empty
                                <p class="text-sm text-slate-600">Sem baixa recorrência relevante na janela atual.</p>
                            @endforelse
                        </div>
                    </section>
                </div>
            </article>
        </section>
    </div>
</x-layouts.app>
