@php
    use App\Helpers\AddressHelper;
    use App\Helpers\DayScheduleHelper;
    use App\Services\Training\TestimonySanitizer;
    use Carbon\Carbon;
    use Illuminate\Support\Collection;

    $course = $training->course;
    $ministry = $course?->ministry;
    $teacher = $training->teacher;
    $church = $training->church;
    $eventAddress = AddressHelper::format_address(
        $training->street ?: $church?->street,
        $training->number ?: $church?->number,
        $training->complement ?: $church?->complement,
        $training->district ?: $church?->district,
        $training->city ?: $church?->city,
        $training->state ?: $church?->state,
        $training->postal_code ?: $church?->postal_code,
    );
    $statusLabel = $training->status?->label() ?? __('Status não definido');
    $bannerUrl = $training->banner ? asset('storage/' . $training->banner) : null;
    $workloadMinutes = $eventDates->reduce(function (int $total, $eventDate): int {
        if (!$eventDate->start_time || !$eventDate->end_time) {
            return $total;
        }

        $start = Carbon::parse($eventDate->date . ' ' . $eventDate->start_time);
        $end = Carbon::parse($eventDate->date . ' ' . $eventDate->end_time);

        if ($end->lessThanOrEqualTo($start)) {
            return $total;
        }

        return $total + $start->diffInMinutes($end);
    }, 0);
    $workloadDuration = '00h';
    if ($workloadMinutes > 0) {
        $hours = intdiv($workloadMinutes, 60);
        $minutes = $workloadMinutes % 60;
        $workloadDuration = $minutes > 0 ? sprintf('%02dh%02d', $hours, $minutes) : sprintf('%02dh', $hours);
    }

    /** @var Collection<int, \App\Models\TrainingScheduleItem> $scheduleItems */
    $scheduleItems = $training->scheduleItems;
    $scheduleItemsByDate = $scheduleItems->groupBy(fn($item) => $item->date?->format('Y-m-d'));
    $dayPlanStatusByDate = $eventDates->mapWithKeys(function ($eventDate) use ($scheduleItemsByDate): array {
        $dateValue = is_string($eventDate->date)
            ? $eventDate->date
            : Carbon::parse((string) $eventDate->date)->format('Y-m-d');

        $itemsForDay = $scheduleItemsByDate->get($dateValue, collect());
        $status = DayScheduleHelper::planStatus($dateValue, $eventDate->end_time, $itemsForDay);

        return [$dateValue => $status];
    });
    $hasOverplannedDay = $eventDates->contains(function ($eventDate) use ($scheduleItemsByDate): bool {
        $dateValue = is_string($eventDate->date)
            ? $eventDate->date
            : Carbon::parse((string) $eventDate->date)->format('Y-m-d');

        $itemsForDay = $scheduleItemsByDate->get($dateValue, collect());
        $status = DayScheduleHelper::planStatus($dateValue, $eventDate->end_time, $itemsForDay);

        return $status === DayScheduleHelper::STATUS_OVER;
    });
    $allDaysMatch = DayScheduleHelper::hasAllDaysMatch($eventDates, $scheduleItems);
    $scheduleBadgeClasses = '';
    $scheduleStatusLabel = __('Carga horária incompleta');

    if ($hasOverplannedDay) {
        $scheduleBadgeClasses .= ' bg-red-100 text-red-700';
        $scheduleStatusLabel = __('Acima do período previsto');
    } elseif ($allDaysMatch) {
        $scheduleBadgeClasses .= ' bg-emerald-100 text-emerald-700';
        $scheduleStatusLabel = __('Adequada ao plano');
    } else {
        $scheduleBadgeClasses .= ' bg-amber-100 text-amber-800';
    }

    $formattedNotes = TestimonySanitizer::sanitize($training->notes);
@endphp

<div class="space-y-4">
    <section id="indicators" class="flex flex-wrap gap-4">
        <div
            class="rounded-lg border border-sky-950/50 bg-linear-to-br from-slate-100 via-white to-slate-200 px-4 py-2.5 text-2xl font-bold flex-auto flex gap-3 items-center">
            <div><img src="{{ asset('images/svg/people-network.svg') }}" alt="indicator" class="h-10"></div>
            <div>
                <div class="text-xs opacity-75">{{ __('Total de alunos:') }}</div>
                {{ $totalRegistrations }}
            </div>
        </div>
        <div
            class="rounded-lg border border-sky-950/50 bg-linear-to-br from-slate-100 via-white to-slate-200 px-4 py-2.5 text-2xl font-bold flex-auto flex gap-3 items-center">
            <div><img src="{{ asset('images/svg/church.svg') }}" alt="indicator" class="h-10"></div>
            <div>
                <div class="text-xs opacity-75">{{ __('Total de igrejas:') }}</div>
                {{ $totalParticipatingChurches }}
            </div>
        </div>
        <div
            class="rounded-lg border border-sky-950/50 bg-linear-to-br from-slate-100 via-white to-slate-200 px-4 py-2.5 text-2xl font-bold flex-auto flex gap-3 items-center">
            <div><img src="{{ asset('images/svg/new-church.svg') }}" alt="indicator" class="h-10"></div>
            <div>
                <div class="text-xs opacity-75">{{ __('Total de igrejas novas:') }}</div>
                {{ $totalNewChurches }}
            </div>
        </div>
        <div
            class="rounded-lg border border-sky-950/50 bg-linear-to-br from-slate-100 via-white to-slate-200 px-4 py-2.5 text-2xl font-bold flex-auto flex gap-3 items-center">
            <div><img src="{{ asset('images/svg/pastor.svg') }}" alt="indicator" class="h-10"></div>
            <div>
                <div class="text-xs opacity-75">{{ __('Total de pastores:') }}</div>
                {{ $totalPastors }}
            </div>
        </div>
        <div
            class="rounded-lg border border-sky-950/50 bg-linear-to-br from-slate-100 via-white to-slate-200 px-4 py-2.5 text-2xl font-bold flex-auto flex gap-3 items-center">
            <div><img src="{{ asset('images/svg/dove.svg') }}" alt="indicator" class="h-10"></div>
            <div>
                <div class="text-xs opacity-75">{{ __('Total de decisões:') }}</div>
                {{ $totalDecisions }}
            </div>
        </div>
    </section>

    <section
        class="rounded-2xl border border-amber-300/30 bg-linear-to-br from-slate-100 via-white to-slate-200 p-6 shadow-lg">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-2">
            <div>
                <h2 class="text-2xl text-blue-900" style="font-family: 'Cinzel', serif;">
                    {{ $course?->type ?? __('Treinamento') }}: <span
                        class="font-semibold text-nowrap">{{ $course?->name ?? __('Curso não definido') }}</span>
                    <span class=""> - {{ $course?->initials ?? '' }}</span>
                </h2>
                <p class="text-sm font-bold text-slate-600">{{ __('Ministério:') }} <span class="font-bold"></span>
                    {{ $training?->course->ministry->name ?? __('-') }}</span></p>
            </div>
            <div class="flex flex-col justify-center items-end gap-1 text-right">
                <p class="text-sm text-slate-700 uppercase">{{ __('Professor Responsável:') }}<span
                        class="font-bold">{{ $training?->teacher->name ?? __('-') }}</span></p>
                <p class="text-sm text-slate-500">
                    (<span class="text-nowrap">{{ $training?->teacher->email ?? __('-') }}</span> /
                    <span class="text-nowrap">{{ $training?->teacher->phone ?? __('-') }}</span>)
                </p>
            </div>
        </div>

        <div class="mt-6 grid gap-6 lg:flex flex-wrap">
            <div class="rounded-2xl border border-slate-800/40 bg-white/90 p-4 basis-3/5 flex-auto">
                <h3 class="text-sm font-semibold text-slate-900 uppercase border-b-2 border-sky-800/30 pb-2 mb-2">
                    {{ __('Igreja Base') }}
                </h3>
                <div class="mt-3 flex flex-col gap-4 text-sm text-slate-700">
                    <div class="flex flex-wrap items-center justify-between gap-x-4  border-b border-sky-100/70">
                        <span class="">{{ __('Nome da Igreja') }}</span>
                        <div></div>
                        <span class="font-semibold text-slate-900">
                            <div class="mt-1 text-slate-900 text-right uppercase">
                                {{ $church?->name ?? __('Não informado') }}
                            </div>
                        </span>
                    </div>
                    <div class="flex items-center justify-between gap-4  border-b border-sky-100/70">
                        <span class="">{{ __('Líder da Clínica') }}<span
                                class="hidden xs:inline">/{{ __('Pastor') }}</span></span>
                        <span
                            class="font-semibold text-slate-900 text-right">{{ $training->leader ?? __(key: 'Não informado') }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4  border-b border-sky-100/70">
                        <span class="">{{ __('Coordenador') }}</span>
                        <span
                            class="font-semibold text-slate-900">{{ $training->coordinator ?? __(key: 'Não informado') }}</span>
                    </div>
                    <x-src.line-theme />
                    <div class="flex flex-wrap gap-4 justify-between items-center">
                        <div class="">{{ __('Telefone') }}</div>
                        <div class="text-xs text-slate-800 flex-auto text-right font-bold">
                            {{ $training->phone ?? __('Não informado') }}</div>
                    </div>
                    <div class="flex flex-wrap gap-4 justify-between items-center">
                        <div class="">{{ __('Email') }}</div>
                        <div class="text-xs text-slate-800 flex-auto text-right font-bold">
                            {{ $training->email ?? __('Não informado') }}
                        </div>
                    </div>
                    <x-src.line-theme />
                    <div class="flex flex-wrap gap-4 justify-between items-center">
                        <div class="">{{ __('Endereço') }}</div>
                        <div class="text-xs text-slate-800 flex-auto text-right font-bold">
                            {{ $eventAddress !== '' ? $eventAddress : __('Não informado') }}
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="rounded-2xl border border-slate-800/40 bg-white/90 p-4 basis-72 flex-auto relative overflow-hidden">
                <h4 class="text-sm font-semibold text-slate-900 uppercase border-b-2 border-sky-800/30 pb-2 mb-2">
                    {{ __('Datas do evento') }}</h4>
                <div class="mt-3 flex flex-col gap-3 text-sm text-slate-700">
                    @forelse ($eventDates as $eventDate)
                        @php
                            $dateValue = is_string($eventDate->date)
                                ? $eventDate->date
                                : \Carbon\Carbon::parse((string) $eventDate->date)->format('Y-m-d');
                            $dayPlanStatus = $dayPlanStatusByDate->get(
                                $dateValue,
                                \App\Helpers\DayScheduleHelper::STATUS_UNDER,
                            );
                            $dayTimeClass =
                                $dayPlanStatus === \App\Helpers\DayScheduleHelper::STATUS_OVER
                                    ? 'text-red-600'
                                    : ($dayPlanStatus === \App\Helpers\DayScheduleHelper::STATUS_UNDER
                                        ? 'text-amber-600'
                                        : 'text-slate-500');
                        @endphp
                        <div class="flex flex-wrap items-center justify-between gap-3 relative">
                            <span class="font-semibold text-slate-900">
                                {{ \Carbon\Carbon::parse($eventDate->date)->format('d/m/Y') }}
                            </span>
                            <span class="{{ $dayTimeClass }}">
                                {{ date('H:i', strtotime($eventDate->start_time)) }} -
                                {{ date('H:i', strtotime($eventDate->end_time)) }}
                            </span>
                            @if ($dayPlanStatus !== \App\Helpers\DayScheduleHelper::STATUS_OK)
                                <img src="{{ asset('images/alarme.webp') }}" alt="alerta"
                                    class="h-3 absolute top-0 -right-3">
                            @endif
                        </div>
                    @empty
                        <span class="text-slate-500">{{ __('Nenhuma data cadastrada.') }}</span>
                    @endforelse
                </div>
                <div
                    class="absolute inset-x-0 bottom-0 px-3 py-1 text-xs font-semibold text-center {{ $scheduleBadgeClasses }}">
                    <div class="uppercase">{{ __('Programação:') }}</div>
                    <div>{{ __('Carga Horária') }}: {{ $workloadDuration }} · {{ $scheduleStatusLabel }}</div>
                </div>

            </div>

            @if ($bannerUrl)
                <div
                    class="rounded-2xl border border-amber-300/30 bg-white p-4 shadow-lg basis-60 h-96 max-h-96 overflow-hidden">
                    <h3 class="text-sm font-semibold text-slate-900 uppercase">{{ __('Banner do treinamento') }}</h3>
                    <img src="{{ $bannerUrl }}" alt="{{ __('Banner do treinamento') }}"
                        class="mt-3 block rounded-xl border border-slate-800/40 object-contain">
                </div>
            @endif

            <div class="rounded-2xl border border-slate-800/40 bg-white/90 p-4 basis-72 flex-auto">
                <h4 class="text-sm font-semibold text-slate-900 uppercase border-b-2 border-sky-800/30 pb-2 mb-2">
                    {{ __('Resumo STP') }}
                </h4>
                <div class="mt-3 grid gap-3 text-sm text-slate-700">
                    <div class="flex items-center justify-between gap-4  border-b border-sky-100/70">
                        <span class="text-slate-500">{{ __('Concluídas / Previstas') }}</span>
                        <span class="font-semibold text-slate-900">
                            {{ $resumoStp['sessoes_concluidas'] }} /
                            {{ $resumoStp['sessoes_previstas'] }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between gap-4  border-b border-sky-100/70">
                        <span class="text-slate-500">{{ __('Quantas vezes?') }}</span>
                        <span class="font-semibold text-slate-900">{{ $resumoStp['evangelho_explicado'] }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4  border-b border-sky-100/70">
                        <span class="text-slate-500">{{ __('Para quantas pessoas?') }}</span>
                        <span class="font-semibold text-slate-900">{{ $resumoStp['pessoas_ouviram'] }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4  border-b border-sky-100/70">
                        <span class="text-slate-500">{{ __('Decisão') }}</span>
                        <span class="font-semibold text-slate-900">{{ $resumoStp['decisao'] }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4  border-b border-sky-100/70">
                        <span class="text-slate-500">{{ __('Sem decisão/ interessado') }}</span>
                        <span class="font-semibold text-slate-900">{{ $resumoStp['sem_decisao_interessado'] }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4  border-b border-sky-100/70">
                        <span class="text-slate-500">{{ __('Rejeição') }}</span>
                        <span class="font-semibold text-slate-900">{{ $resumoStp['rejeicao'] }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4  border-b border-sky-100/70">
                        <span class="text-slate-500">{{ __('Para segurança/Já é crente') }}</span>
                        <span
                            class="font-semibold text-slate-900">{{ $resumoStp['para_seguranca_ja_e_crente'] }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4  border-b border-sky-100/70">
                        <span class="text-slate-500">{{ __('Visita agendada (7 dias após)') }}</span>
                        <span class="font-semibold text-slate-900">{{ $resumoStp['visita_agendada'] }}</span>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-800/40 bg-white/90 p-4 flex-auto">
                <h4 class="text-sm font-semibold text-slate-900 uppercase border-b-2 border-sky-800/30 pb-2 mb-2">
                    {{ __('Valores por inscrição') }}</h4>
                <div class="mt-3 flex flex-col gap-3 text-sm text-slate-700">
                    <div class="flex items-center justify-between gap-4  border-b border-sky-100/70">
                        <span class="text-slate-500">{{ __('Preço') }}</span>
                        <span class="font-semibold text-slate-900">{{ $training->price ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4  border-b border-sky-100/70">
                        <span class="text-slate-500">{{ __('Despesas Extras') }}</span>
                        <span class="font-semibold text-slate-900">{{ $training->price_church ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4  border-b border-sky-100/70">
                        <span class="text-slate-500">{{ __('Desconto') }}</span>
                        <span class="font-semibold text-slate-900">{{ $training->discount ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4  border-b border-sky-100/70">
                        <span class="text-slate-500">{{ __('Total por cada inscrição') }}</span>
                        <span class="font-semibold text-slate-900">{{ $training->payment ?? '-' }}</span>
                    </div>
                </div>
                <h4 class="text-sm font-semibold text-slate-900 uppercase border-b-2 border-sky-800/30 pt-4 pb-2 mb-2">
                    {{ __('Resumo financeiro') }}</h4>
                <div class="mt-3 flex flex-col gap-3 text-sm text-slate-700">
                    <div class="flex items-center justify-between gap-4  border-b border-sky-100/70">
                        <span class="text-slate-500">{{ __('Total de pagantes') }}</span>
                        <span class="font-semibold text-slate-900">
                            {{ $paidStudentsCount }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between gap-4  border-b border-sky-100/70">
                        <span class="text-slate-500">{{ __('Receita') }} <span
                                class="hidden xs:inline">{{ __('total de inscrições') }}</span></span>
                        <span class="font-semibold text-slate-900">
                            {{ $totalReceivedFromRegistrations ?? '-' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between gap-4  border-b border-sky-100/70">
                        <span class="text-slate-500">{{ __('Repasse ao ') }} <span
                                class="hidden xs:inline">{{ __('Ministério Nacional de') }}</span>
                            {{ __(' EE') }}</span>
                        <span class="font-semibold text-slate-900">
                            {{ $eeMinistryBalance ?? '-' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between gap-4  border-b border-sky-100/70">
                        <span class="text-slate-500">{{ __('Repasse para') }} <span
                                class="hidden xs:inline">{{ __('despesas da') }}</span>
                            {{ __('igreja base') }}</span>
                        <span class="font-semibold text-slate-900">
                            {{ $hostChurchExpenseBalance ?? '-' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section
        class="rounded-2xl border border-amber-300/30 bg-linear-to-br from-slate-100 via-white to-slate-200 p-6 shadow-lg">
        <h4 class="text-sm font-semibold text-slate-900 uppercase">{{ __('Testemunho do professor') }}</h4>
        @if ($formattedNotes)
            <div class="mt-3 space-y-3 text-sm leading-6 text-slate-700">
                {!! $formattedNotes !!}
            </div>
        @else
            <p class="mt-3 text-sm text-slate-700">
                {{ __('Nenhum testemunho registrado.') }}
            </p>
        @endif
    </section>

    {{-- @if ($bannerUrl)
        <section class="rounded-2xl border border-amber-300/30 bg-white p-4 shadow-lg">
            <h3 class="text-sm font-semibold text-slate-900 uppercase">{{ __('Banner do treinamento') }}</h3>
            <div class="mt-3 overflow-hidden rounded-xl border border-slate-800/40">
                <img src="{{ $bannerUrl }}" alt="{{ __('Banner do treinamento') }}"
                    class="h-64 w-full object-cover">
            </div>
        </section>
    @endif --}}
</div>
