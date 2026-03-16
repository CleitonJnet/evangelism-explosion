<x-layouts.app :title="__('Contexto do Treinamento')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <x-app.portal.page-header eyebrow="Portal Base" title="Contexto do treinamento"
            description="Aqui voce acompanha o evento em que serve sem assumir automaticamente a gestao completa da base anfitria."
            :breadcrumbs="[
                ['label' => 'Portais', 'url' => route('app.start')],
                ['label' => 'Base e Treinamentos', 'url' => route('app.portal.base.dashboard')],
                ['label' => 'Treinamentos em que Sirvo', 'url' => route('app.portal.base.serving')],
                ['label' => 'Contexto do treinamento', 'current' => true],
            ]" />

        @php
            $eventDates = $training->eventDates;
            $firstDate = $eventDates->first();
            $lastDate = $eventDates->last();
            $scheduleSummary = match (true) {
                $firstDate === null => __('Datas a confirmar'),
                $lastDate !== null && $firstDate->date !== $lastDate->date => __('De :start ate :end', [
                    'start' => \Illuminate\Support\Carbon::parse((string) $firstDate->date)->format('d/m/Y'),
                    'end' => \Illuminate\Support\Carbon::parse((string) $lastDate->date)->format('d/m/Y'),
                ]),
                default => \Illuminate\Support\Carbon::parse((string) $firstDate->date)->format('d/m/Y'),
            };
        @endphp

        <section class="grid gap-4 lg:grid-cols-[minmax(0,2fr)_minmax(18rem,1fr)]">
            <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-5">
                    <div class="space-y-3">
                        <div class="flex flex-wrap gap-2">
                            @foreach ($assignments as $assignment)
                                <span class="rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-sky-800">
                                    {{ $assignment }}
                                </span>
                            @endforeach

                            @if ($assignments === [])
                                <span class="rounded-full bg-neutral-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-neutral-600">
                                    {{ __('Atuacao operacional') }}
                                </span>
                            @endif
                        </div>

                        <div>
                            <h2 class="text-2xl font-semibold text-neutral-950">{{ $training->course?->name ?? __('Treinamento') }}</h2>
                            <p class="text-sm text-neutral-600">
                                {{ $training->course?->type ? $training->course->type . ' - ' : '' }}{{ $training->church?->name ?? __('Base nao informada') }}
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4">
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-neutral-500">{{ __('Agenda') }}</div>
                            <div class="mt-2 text-sm text-neutral-800">{{ $scheduleSummary }}</div>
                        </div>

                        <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4">
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-neutral-500">{{ __('Local') }}</div>
                            <div class="mt-2 text-sm text-neutral-800">
                                {{ collect([$training->city, $training->state])->filter()->implode(', ') ?: __('Cidade e UF nao informadas') }}
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                        {{ __('Este contexto existe para orientar sua atuacao no evento. Cadastros da base anfitria, acervo e governanca institucional continuam restritos aos fluxos apropriados.') }}
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                <div class="space-y-4">
                    <div>
                        <h3 class="text-lg font-semibold text-neutral-950">{{ __('Acoes disponiveis') }}</h3>
                        <p class="text-sm text-neutral-600">{{ __('Abrimos apenas o que faz sentido para o seu papel neste treinamento.') }}</p>
                    </div>

                    <div class="grid gap-3">
                        @if (isset($actions['details']))
                            <a href="{{ $actions['details'] }}" class="inline-flex items-center justify-center rounded-2xl bg-sky-950 px-4 py-3 text-sm font-semibold text-white transition hover:bg-sky-900">
                                {{ __('Abrir detalhes operacionais') }}
                            </a>
                        @endif

                        @if (isset($actions['schedule']))
                            <a href="{{ $actions['schedule'] }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-sky-300 hover:text-sky-900">
                                {{ __('Abrir agenda') }}
                            </a>
                        @endif

                        @if (isset($actions['registrations']))
                            <a href="{{ $actions['registrations'] }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-sky-300 hover:text-sky-900">
                                {{ __('Abrir inscricoes') }}
                            </a>
                        @endif

                        @if (isset($actions['statistics']))
                            <a href="{{ $actions['statistics'] }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-sky-300 hover:text-sky-900">
                                {{ __('Abrir estatisticas') }}
                            </a>
                        @endif

                        @if (isset($actions['stp']))
                            <a href="{{ $actions['stp'] }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-sky-300 hover:text-sky-900">
                                {{ __('Abrir STP e abordagens') }}
                            </a>
                        @endif

                        @if (isset($actions['report']))
                            <a href="{{ $actions['report'] }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-sky-300 hover:text-sky-900">
                                {{ __('Abrir relato') }}
                            </a>
                        @endif

                        @if (isset($actions['ojt']))
                            <a href="{{ $actions['ojt'] }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-sky-300 hover:text-sky-900">
                                {{ __('Abrir OJT') }}
                            </a>
                        @endif

                        @if ($actions === [])
                            <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 p-4 text-sm text-neutral-600">
                                {{ __('Nenhuma acao operacional adicional foi liberada para este treinamento no seu contexto atual.') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-layouts.app>
