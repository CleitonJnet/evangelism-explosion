<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-2xl">
    <section class="rounded-[1.75rem] border border-sky-100 bg-linear-to-br from-white via-sky-50/70 to-amber-50/60 p-6 shadow-sm">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
            <div class="max-w-3xl">
                <div class="inline-flex items-center rounded-full bg-sky-950 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-white">
                    {{ __('Infraestrutura de Dashboard') }}
                </div>
                <h1 class="mt-4 text-3xl font-semibold tracking-tight text-sky-950">
                    {{ __('Base técnica compartilhada para Professor e Diretor') }}
                </h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">
                    {{ __('Esta página valida payloads, componentes de gráfico, filtro temporal e integração com Livewire sem antecipar o dashboard final de produção.') }}
                </p>
            </div>

            <div class="rounded-2xl border border-sky-200 bg-white/90 px-4 py-3 text-sm text-slate-700 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Contexto ativo') }}</div>
                <div class="mt-1 text-lg font-semibold text-sky-950">{{ $context === 'director' ? __('Diretor') : __('Professor') }}</div>
                <div class="mt-2 text-xs text-slate-500">{{ __('Período padrão: anual, persistido por query string.') }}</div>
            </div>
        </div>

        <div class="mt-6">
            <x-dashboard.period-filter model="period" :options="$this->periodOptions" :selected="$period" />
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach ($this->dashboard['kpis'] as $kpi)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $kpi['label'] }}</div>
                <div class="mt-2 text-3xl font-bold text-slate-900">{{ $kpi['value'] }}</div>
                @if ($kpi['description'])
                    <div class="mt-2 text-sm text-slate-600">{{ $kpi['description'] }}</div>
                @endif
                @if ($kpi['trend'])
                    <div class="mt-3 inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                        {{ $kpi['trend'] }}
                    </div>
                @endif
            </article>
        @endforeach
    </section>

    <section class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)]">
        <div class="grid gap-6">
            @foreach (array_slice($this->dashboard['charts'], 0, 2) as $chart)
                <x-dashboard.chart :chart="$chart" />
            @endforeach
        </div>

        <div class="grid gap-6">
            @if (isset($this->dashboard['charts'][2]))
                <x-dashboard.chart :chart="$this->dashboard['charts'][2]" />
            @endif

            @foreach ($this->dashboard['tables'] as $table)
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold text-sky-950">{{ $table['title'] }}</h2>
                            <p class="text-xs text-slate-500">{{ __('Estrutura pronta para rankings operacionais.') }}</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    @foreach ($table['columns'] as $column)
                                        <th class="px-2 py-2 font-semibold">{{ $column }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @foreach ($table['rows'] as $row)
                                    <tr>
                                        <td class="px-2 py-3 font-semibold text-slate-500">{{ $row['position'] }}</td>
                                        <td class="px-2 py-3 font-semibold text-slate-900">{{ $row['label'] }}</td>
                                        <td class="px-2 py-3 text-slate-700">{{ $row['value'] }}</td>
                                        <td class="px-2 py-3 text-slate-500">{{ $row['context'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
</div>
