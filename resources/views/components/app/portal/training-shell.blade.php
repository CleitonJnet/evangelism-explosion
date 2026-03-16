@props([
    'training',
    'tabs' => [],
    'activeTab' => 'show',
    'capabilities' => [],
    'portalCapabilities' => [],
    'assignments' => [],
    'trainingContext' => null,
    'portalLabel' => 'Portal Base e Treinamentos',
    'portalRoles' => [],
    'areaCards' => [],
    'reportSummary' => [],
])

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
    $roleSummary = $assignments !== [] ? implode(' • ', $assignments) : __('Leitura contextual do evento');
@endphp

<div class="flex h-full w-full flex-1 flex-col gap-6">
    <x-app.portal.page-header eyebrow="Portal Base" title="Evento no Portal Base"
        :description="__('A mesma experiencia de treinamento, filtrada pelas capabilities do seu contexto atual dentro da base e do evento.')"
        :breadcrumbs="[
            ['label' => 'Portais', 'url' => route('app.start')],
            ['label' => 'Base e Treinamentos', 'url' => route('app.portal.base.dashboard')],
            ['label' => 'Eventos da Base', 'url' => route('app.portal.base.events')],
            ['label' => 'Evento', 'current' => true],
        ]" />

    <section class="grid gap-4 xl:grid-cols-[minmax(0,2fr)_minmax(18rem,1fr)]">
        <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-5">
                <div class="flex flex-wrap gap-2">
                    <span class="rounded-full bg-sky-950 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-white">
                        {{ $portalLabel }}
                    </span>
                    @if ($trainingContext)
                        <span class="rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-sky-800">
                            {{ $trainingContext }}
                        </span>
                    @endif
                    @foreach ($assignments as $assignment)
                        <span class="rounded-full bg-neutral-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-neutral-700">
                            {{ $assignment }}
                        </span>
                    @endforeach
                </div>

                <div>
                    <h2 class="text-2xl font-semibold text-neutral-950">{{ $training->course?->name ?? __('Treinamento') }}</h2>
                    <p class="text-sm text-neutral-600">
                        {{ $training->course?->type ? $training->course->type . ' - ' : '' }}{{ $training->church?->name ?? __('Base nao informada') }}
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4">
                        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-neutral-500">{{ __('Portal atual') }}</div>
                        <div class="mt-2 text-sm text-neutral-800">{{ $portalLabel }}</div>
                    </div>
                    <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4">
                        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-neutral-500">{{ __('Contexto do evento') }}</div>
                        <div class="mt-2 text-sm text-neutral-800">{{ $trainingContext ?? __('Evento no Portal Base') }}</div>
                    </div>
                    <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4">
                        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-neutral-500">{{ __('Atuacoes neste evento') }}</div>
                        <div class="mt-2 text-sm text-neutral-800">{{ $roleSummary }}</div>
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
            </div>
        </div>

        <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
            <div class="space-y-4">
                <div>
                    <h3 class="text-lg font-semibold text-neutral-950">{{ __('Matriz de capabilities') }}</h3>
                    <p class="text-sm text-neutral-600">{{ __('A UI e o backend leem a mesma matriz explicita do Portal Base e Treinamentos.') }}</p>
                </div>

                <div class="grid gap-3 text-sm text-neutral-700">
                    <div class="flex items-center justify-between gap-3">
                        <span>{{ __('Visao geral da base e do evento') }}</span>
                        <span class="font-semibold text-neutral-950">{{ ($portalCapabilities['viewBaseOverview'] ?? false) ? __('Liberada') : __('Bloqueada') }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span>{{ __('Inscricoes do treinamento') }}</span>
                        <span class="font-semibold text-neutral-950">{{ ($portalCapabilities['manageTrainingRegistrations'] ?? false) ? __('Gerenciadas') : __('Somente leitura') }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span>{{ __('Programacao ministerial') }}</span>
                        <span class="font-semibold text-neutral-950">{{ ($portalCapabilities['manageEventSchedule'] ?? false) ? __('Gerenciavel') : __('Visualizacao') }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span>{{ __('Mentores e facilitadores') }}</span>
                        <span class="font-semibold text-neutral-950">{{ (($portalCapabilities['manageMentors'] ?? false) || ($portalCapabilities['manageFacilitators'] ?? false)) ? __('Disponivel') : __('Nao aplicavel') }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span>{{ __('Materiais e acervo local') }}</span>
                        <span class="font-semibold text-neutral-950">{{ ($portalCapabilities['viewEventMaterials'] ?? false) ? __('Disponivel') : __('Oculto') }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span>{{ __('Relatorios do evento') }}</span>
                        <span class="font-semibold text-neutral-950">{{ (($portalCapabilities['submitChurchEventReport'] ?? false) || ($portalCapabilities['submitTeacherEventReport'] ?? false)) ? __('Habilitados') : __('Nao habilitados') }}</span>
                    </div>
                </div>

                @if ($portalRoles !== [])
                    <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 p-4 text-sm text-neutral-600">
                        {{ __('Perfis ativos no portal: :roles', ['roles' => implode(', ', $portalRoles)]) }}
                    </div>
                @endif

                @if ($reportSummary !== [])
                    <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h4 class="text-sm font-semibold text-neutral-950">{{ __('Status dos relatorios') }}</h4>
                                <p class="text-xs text-neutral-500">{{ __('Leitura compartilhada do andamento da igreja-base e do professor neste evento.') }}</p>
                            </div>
                            <a href="{{ route('app.portal.base.trainings.reports', $training) }}" class="text-xs font-semibold text-sky-900 transition hover:text-sky-700">
                                {{ __('Abrir fluxo') }}
                            </a>
                        </div>

                        <div class="mt-4 grid gap-3">
                            @foreach ($reportSummary as $report)
                                @php
                                    $statusClasses = match ($report['tone'] ?? 'slate') {
                                        'emerald' => 'bg-emerald-100 text-emerald-800',
                                        'amber' => 'bg-amber-100 text-amber-800',
                                        'sky' => 'bg-sky-100 text-sky-800',
                                        default => 'bg-slate-100 text-slate-700',
                                    };
                                @endphp

                                <div class="rounded-2xl border border-neutral-200 bg-white px-4 py-3">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <div class="text-sm font-semibold text-neutral-950">{{ $report['label'] }}</div>
                                            <div class="mt-1 text-xs text-neutral-500">{{ $report['description'] }}</div>
                                        </div>
                                        <span class="rounded-full px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] {{ $statusClasses }}">
                                            {{ $report['status_label'] }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>

    @if ($areaCards !== [])
        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($areaCards as $area)
                @php
                    $isActive = $activeTab === $area['key'];
                    $toneClasses = match ($area['tone']) {
                        'sky' => 'border-sky-200 bg-sky-50/80',
                        'emerald' => 'border-emerald-200 bg-emerald-50/80',
                        'amber' => 'border-amber-200 bg-amber-50/80',
                        'violet' => 'border-violet-200 bg-violet-50/80',
                        'slate' => 'border-slate-200 bg-slate-50/80',
                        default => 'border-neutral-200 bg-neutral-50/80',
                    };
                @endphp

                @if ($area['route'])
                    <a href="{{ $area['route'] }}"
                        class="rounded-3xl border p-5 shadow-sm transition {{ $isActive ? 'border-sky-900 bg-sky-950 text-white' : $toneClasses . ' text-neutral-900 hover:border-sky-300' }}">
                        <div class="flex items-center justify-between gap-3">
                            <h3 class="text-base font-semibold">{{ $area['label'] }}</h3>
                            <span class="text-xs font-semibold uppercase tracking-[0.18em] {{ $isActive ? 'text-white/80' : ($area['available'] ? 'text-neutral-600' : 'text-neutral-500') }}">
                                {{ $area['available'] ? __('Disponivel') : __('Restrito') }}
                            </span>
                        </div>
                        <p class="mt-3 text-sm {{ $isActive ? 'text-white/85' : 'text-neutral-600' }}">{{ $area['description'] }}</p>
                    </a>
                @else
                    <div class="rounded-3xl border border-dashed border-neutral-300 bg-neutral-50/80 p-5 shadow-sm">
                        <div class="flex items-center justify-between gap-3">
                            <h3 class="text-base font-semibold text-neutral-900">{{ $area['label'] }}</h3>
                            <span class="text-xs font-semibold uppercase tracking-[0.18em] text-neutral-500">{{ __('Em breve') }}</span>
                        </div>
                        <p class="mt-3 text-sm text-neutral-600">{{ $area['description'] }}</p>
                    </div>
                @endif
            @endforeach
        </section>
    @endif

    <nav class="flex flex-wrap gap-2">
        @foreach ($tabs as $tab)
            <a href="{{ $tab['route'] }}"
                class="inline-flex items-center rounded-full border px-4 py-2 text-sm font-semibold transition {{ $activeTab === $tab['key'] ? 'border-sky-900 bg-sky-950 text-white' : 'border-neutral-300 bg-white text-neutral-700 hover:border-sky-300 hover:text-sky-900' }}">
                {{ $tab['label'] }}
            </a>
        @endforeach
    </nav>

    <div>
        {{ $slot }}
    </div>
</div>
