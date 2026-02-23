<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-center justify-between gap-3 border-b border-slate-200 pb-4">
                <div>
                    <h2 class="text-xl font-semibold text-sky-950">{{ __('Próximos Treinamentos') }}</h2>
                    <p class="text-sm text-slate-600">{{ __('Seus próximos eventos com acesso rápido às principais ações.') }}</p>
                </div>
                <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                    {{ __('Total: :count', ['count' => $upcomingTrainings->count()]) }}
                </span>
            </div>

            @if ($upcomingTrainings->isEmpty())
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                    {{ __('Nenhum treinamento futuro encontrado para este professor.') }}
                </div>
            @else
                <div class="grid gap-3">
                    @foreach ($upcomingTrainings as $item)
                        <article class="rounded-xl border border-slate-200 bg-slate-50/60 p-4">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <h3 class="font-semibold text-sky-950">{{ $item['course_label'] }}</h3>
                                    <div class="text-sm text-slate-600">{{ __('Datas: :dates', ['dates' => $item['date_range_label']]) }}</div>
                                </div>
                                <span class="inline-flex items-center rounded-full bg-sky-950 px-2.5 py-1 text-xs font-semibold text-white">
                                    {{ $item['status_label'] }}
                                </span>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-2">
                                <a href="{{ route('app.teacher.trainings.show', $item['training']) }}"
                                    class="inline-flex items-center rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-sky-950 transition hover:border-sky-950">
                                    {{ __('Detalhes') }}
                                </a>
                                <a href="{{ route('app.teacher.trainings.schedule', $item['training']) }}"
                                    class="inline-flex items-center rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-sky-950 transition hover:border-sky-950">
                                    {{ __('Programação') }}
                                </a>
                                <a href="{{ route('app.teacher.trainings.registrations', $item['training']) }}"
                                    class="inline-flex items-center rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-sky-950 transition hover:border-sky-950">
                                    {{ __('Inscrições') }}
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between gap-3 border-b border-slate-200 pb-4">
                    <h2 class="text-lg font-semibold text-sky-950">{{ __('Pendências') }}</h2>
                    <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                        {{ __('Itens: :count', ['count' => $pendingTrainings->count()]) }}
                    </span>
                </div>

                @if ($pendingTrainings->isEmpty())
                    <p class="text-sm text-slate-600">{{ __('Sem pendências operacionais no momento.') }}</p>
                @else
                    <div class="grid gap-3">
                        @foreach ($pendingTrainings as $item)
                            <article class="rounded-xl border border-slate-200 bg-slate-50/60 p-4">
                                <h3 class="font-semibold text-sky-950">{{ $item['course_label'] }}</h3>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @if ($item['has_schedule_issue'])
                                        <span class="inline-flex items-center rounded-md bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-200">
                                            {{ __('Programação pendente') }}
                                        </span>
                                    @endif
                                    @if ($item['has_registration_issue'])
                                        <span class="inline-flex items-center rounded-md bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-200">
                                            {{ __('Inscrições com igreja ausente/pendente') }}
                                        </span>
                                    @endif
                                </div>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <a href="{{ route('app.teacher.trainings.show', $item['training']) }}"
                                        class="inline-flex items-center rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-sky-950 transition hover:border-sky-950">
                                        {{ __('Abrir treinamento') }}
                                    </a>
                                    <a href="{{ route('app.teacher.trainings.registrations', $item['training']) }}"
                                        class="inline-flex items-center rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-sky-950 transition hover:border-sky-950">
                                        {{ __('Revisar inscrições') }}
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 border-b border-slate-200 pb-4 text-lg font-semibold text-sky-950">{{ __('Ações rápidas') }}</h2>
                <div class="grid gap-2">
                    <a href="{{ route('app.teacher.trainings.create') }}"
                        class="inline-flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50/60 px-4 py-2 text-sm font-semibold text-sky-950 transition hover:border-sky-950">
                        <span>{{ __('Criar treinamento') }}</span>
                        <span class="text-amber-500">●</span>
                    </a>
                    <a href="{{ route('app.teacher.trainings.planning') }}"
                        class="inline-flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50/60 px-4 py-2 text-sm font-semibold text-sky-950 transition hover:border-sky-950">
                        <span>{{ __('Lista: Planejamento') }}</span>
                        <span class="text-amber-500">●</span>
                    </a>
                    <a href="{{ route('app.teacher.trainings.scheduled') }}"
                        class="inline-flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50/60 px-4 py-2 text-sm font-semibold text-sky-950 transition hover:border-sky-950">
                        <span>{{ __('Lista: Agendados') }}</span>
                        <span class="text-amber-500">●</span>
                    </a>

                    @if ($quickAccessTraining)
                        <div class="mt-2 border-t border-slate-200 pt-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                            {{ __('Ações no próximo treinamento') }}
                        </div>
                        <a href="{{ route('app.teacher.trainings.registrations', $quickAccessTraining['training']) }}"
                            class="inline-flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50/60 px-4 py-2 text-sm font-semibold text-sky-950 transition hover:border-sky-950">
                            <span>{{ __('Ir para inscrições') }}</span>
                            <span class="text-amber-500">●</span>
                        </a>
                        <a href="{{ route('app.teacher.trainings.show', $quickAccessTraining['training']) }}"
                            class="inline-flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50/60 px-4 py-2 text-sm font-semibold text-sky-950 transition hover:border-sky-950">
                            <span>{{ __('Ir para mentores (detalhes)') }}</span>
                            <span class="text-amber-500">●</span>
                        </a>
                        <a href="{{ route('app.teacher.trainings.stp.approaches', $quickAccessTraining['training']) }}"
                            class="inline-flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50/60 px-4 py-2 text-sm font-semibold text-sky-950 transition hover:border-sky-950">
                            <span>{{ __('Ir para STP') }}</span>
                            <span class="text-amber-500">●</span>
                        </a>
                    @endif
                </div>
            </section>
        </div>
    </div>
</x-layouts.app>
