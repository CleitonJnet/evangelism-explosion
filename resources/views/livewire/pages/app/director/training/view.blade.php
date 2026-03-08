@php
    use App\Helpers\AddressHelper;
    use App\Helpers\DayScheduleHelper;
    use App\Services\Training\TestimonySanitizer;
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Collection;

    $course = $training->course;
    $ministry = $course?->ministry;
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
    $normalizedBannerPath = ltrim(trim((string) $training->banner), '/');
    $hasUploadedBanner = $normalizedBannerPath !== '' && Storage::disk('public')->exists($normalizedBannerPath);
    $defaultBannerUrl = asset('images/banner-default.webp');
    $bannerUrl = $hasUploadedBanner ? asset('storage/' . $normalizedBannerPath) : $defaultBannerUrl;
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
    $requiresCompletionReview = $training->requiresCompletionReview();
    $completionReviewAlertMessage = $training->completionReviewAlertMessage();
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
                @if ($requiresCompletionReview)
                    <div class="mt-3 flex">
                        <span title="{{ $completionReviewAlertMessage }}"
                            class="inline-flex items-center gap-2 rounded-full border border-red-600 bg-red-50/50 px-3 py-1 text-xs font-semibold text-red-700">
                            <span
                                class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-white text-red-600 shadow-sm">
                                <svg version="1.0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 194 178"
                                    preserveAspectRatio="xMidYMid meet" aria-hidden="true" class="h-3.5 w-3.5"
                                    fill="currentColor">
                                    <g transform="translate(0.000000,178.000000) scale(0.100000,-0.100000)"
                                        stroke="none">
                                        <path
                                            d="M825 1722 c-83 -41 -129 -94 -225 -262 -46 -80 -191 -331 -322 -559 -266 -459 -280 -493 -261 -600 31 -165 143 -273 302 -291 100 -12 1238 -11 1311 1 158 25 262 128 292 287 19 103 0 154 -165 439 -79 136 -224 388 -322 558 -101 177 -196 329 -219 353 -67 71 -124 95 -236 99 -89 4 -99 2 -155 -25z m255 -43 c97 -44 101 -49 268 -339 55 -96 190 -330 299 -520 110 -190 206 -362 213 -384 8 -24 11 -67 8 -111 -6 -86 -34 -143 -99 -199 -80 -68 -59 -67 -815 -64 l-679 3 -49 25 c-61 31 -120 97 -140 157 -19 55 -21 146 -4 194 15 43 630 1112 667 1159 16 19 56 50 90 68 53 27 73 32 129 32 46 0 81 -7 112 -21z" />
                                        <path
                                            d="M885 1561 c-16 -10 -37 -27 -46 -37 -14 -17 -155 -258 -541 -931 -55 -95 -104 -189 -109 -208 -19 -66 25 -156 92 -190 42 -22 1336 -22 1378 0 67 34 111 124 92 190 -5 19 -63 128 -129 242 -366 639 -508 880 -526 902 -45 51 -153 68 -211 32z m178 -388 c2 -5 -2 -109 -9 -233 -7 -124 -13 -240 -13 -257 l-1 -33 -68 0 -69 0 -6 113 c-4 61 -10 181 -13 265 l-7 152 91 0 c50 0 93 -3 95 -7z m-49 -623 c35 -13 59 -64 52 -106 -14 -70 -106 -99 -163 -50 -23 20 -28 32 -27 70 0 25 7 53 15 62 26 32 77 42 123 24z" />
                                    </g>
                                </svg>
                            </span>
                            {{ __('Reviso os dados do evento e marque como CONCLUÍDO, ou se foi cancelado, marque-o como CANCELADO') }}
                        </span>
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-6 grid gap-6 lg:flex flex-wrap">
            <div
                class="rounded-2xl border border-amber-300/30 bg-white p-4 shadow-lg basis-64 h-96 max-h-96 overflow-hidden">
                <h3 class="text-sm font-semibold text-slate-900 uppercase">{{ __('Banner do treinamento') }}</h3>
                <div class="relative mt-3 h-80 w-full overflow-hidden rounded-xl border border-slate-800/40">
                    <img src="{{ $bannerUrl }}" alt="{{ __('Banner do treinamento') }}"
                        class="h-full w-full object-cover"
                        onerror="this.onerror=null;this.src='{{ $defaultBannerUrl }}';">

                    @if (!$hasUploadedBanner)
                        <div class="absolute inset-0 flex items-center justify-center bg-slate-900/35 p-4 text-center">
                            <span class="rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-slate-800">
                                {{ __('Nenhum banner foi enviado para este evento') }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            <livewire:pages.app.director.training.event-teachers :training-id="$training->id"
                wire:key="training-event-teachers-{{ $training->id }}" />


            <div class="rounded-2xl border border-slate-800/40 bg-white/90 p-4 basis-150 flex-auto">
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
                class="rounded-2xl border border-slate-800/40 bg-white/90 p-4 basis-60 flex-auto relative overflow-hidden">
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

            <div class="rounded-2xl border border-slate-800/40 bg-white/90 p-4 basis-62 flex-auto">
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

            <div class="rounded-2xl border border-slate-800/40 bg-white/90 p-4 basis-70 flex-auto">
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
                                class="hidden 3xl:inline">{{ __('Ministério Nacional de') }}</span>
                            {{ __(' EE') }}</span>
                        <span class="font-semibold text-slate-900">
                            {{ $eeMinistryBalance ?? '-' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between gap-4  border-b border-sky-100/70">
                        <span class="text-slate-500">{{ __('Repasse para') }} <span
                                class="hidden 3xl:inline">{{ __('despesas da') }}</span>
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
        <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-200 pb-3">
            <div>
                <h4 class="text-sm font-semibold uppercase text-slate-900">{{ __('Apoio operacional de materiais') }}</h4>
                <p class="mt-1 text-sm text-slate-600">
                    {{ __('O treinamento aproveita os materiais vinculados ao curso. O financeiro continua separado da entrega física.') }}
                </p>
            </div>

            <x-src.btn-gold type="button"
                x-on:click.prevent="$dispatch('open-training-material-delivery-modal', { trainingId: {{ $training->id }} })">
                {{ __('Registrar entrega') }}
            </x-src.btn-gold>
        </div>

        <div class="mt-5 grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)]">
            <div class="space-y-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                    <h5 class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Materiais vinculados ao curso') }}</h5>

                    @if ($courseMaterials->isEmpty())
                        <p class="mt-3 text-sm text-slate-600">
                            {{ __('Nenhum material foi vinculado a este curso ainda.') }}
                        </p>
                    @else
                        <div class="mt-3 grid gap-3">
                            @foreach ($courseMaterials as $material)
                                <div class="rounded-xl border border-slate-200 px-4 py-3">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div>
                                            <div class="font-semibold text-slate-900">{{ $material->name }}</div>
                                            <div class="mt-1 text-xs uppercase text-slate-500">
                                                {{ $material->isComposite() ? __('Composto') : __('Simples') }}
                                                · {{ __('Estoque mínimo') }}: {{ $material->minimum_stock }}
                                            </div>
                                        </div>

                                        @if ($material->price)
                                            <div class="text-sm font-semibold text-slate-700">{{ $material->price }}</div>
                                        @endif
                                    </div>

                                    @if ($material->isComposite() && $material->components->isNotEmpty())
                                        <div class="mt-3 border-t border-slate-100 pt-3">
                                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                {{ __('Composição do kit') }}
                                            </div>
                                            <div class="mt-2 flex flex-wrap gap-2">
                                                @foreach ($material->components as $component)
                                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                                        {{ $component->componentMaterial?->name ?? __('Componente removido') }} x{{ $component->quantity }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                    <h5 class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Kits recomendados para este curso') }}</h5>

                    @if ($recommendedKits->isEmpty())
                        <p class="mt-3 text-sm text-slate-600">
                            {{ __('Nenhum material composto foi vinculado a este curso ainda.') }}
                        </p>
                    @else
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($recommendedKits as $kit)
                                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-900">
                                    {{ $kit->name }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="space-y-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                    <h5 class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Resumo de consumo') }}</h5>

                    @if ($consumedMaterialsSummary->isEmpty())
                        <p class="mt-3 text-sm text-slate-600">
                            {{ __('Nenhuma saída de material foi vinculada a este treinamento ainda.') }}
                        </p>
                    @else
                        <div class="mt-3 grid gap-3">
                            @foreach ($consumedMaterialsSummary as $summary)
                                <div class="flex items-center justify-between gap-4 rounded-xl border border-slate-200 px-4 py-3">
                                    <div>
                                        <div class="font-semibold text-slate-900">{{ $summary['material_name'] }}</div>
                                        <div class="text-xs uppercase text-slate-500">{{ $summary['type'] }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-bold text-slate-900">{{ $summary['quantity'] }}</div>
                                        <div class="text-xs text-slate-500">{{ __('unidades') }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                    <h5 class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Histórico de consumo auditável') }}</h5>
                    <div class="mt-1 text-xs text-slate-500">
                        {{ __('Vínculo auditável com o treinamento #:id', ['id' => $training->id]) }}
                    </div>

                    @if ($trainingStockMovements->isEmpty())
                        <p class="mt-3 text-sm text-slate-600">
                            {{ __('Ainda não existem movimentações de estoque vinculadas a este treinamento.') }}
                        </p>
                    @else
                        <div class="mt-3 overflow-x-auto">
                            <table class="w-full min-w-[44rem] text-left text-sm">
                                <thead class="bg-slate-100 text-xs uppercase text-slate-600">
                                    <tr>
                                        <th class="px-3 py-2">{{ __('Data/hora') }}</th>
                                        <th class="px-3 py-2">{{ __('Estoque') }}</th>
                                        <th class="px-3 py-2">{{ __('Material') }}</th>
                                        <th class="px-3 py-2">{{ __('Tipo') }}</th>
                                        <th class="px-3 py-2 text-right">{{ __('Qtd.') }}</th>
                                        <th class="px-3 py-2 text-right">{{ __('Saldo após') }}</th>
                                        <th class="px-3 py-2">{{ __('Usuário') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200">
                                    @foreach ($trainingStockMovements as $movement)
                                        <tr class="odd:bg-white even:bg-slate-50/60">
                                            <td class="px-3 py-2 text-slate-700">
                                                {{ $movement->created_at?->format('d/m/Y H:i') ?? '-' }}
                                            </td>
                                            <td class="px-3 py-2 text-slate-700">{{ $movement->inventory?->name ?? '-' }}</td>
                                            <td class="px-3 py-2 font-semibold text-slate-900">{{ $movement->material?->name ?? '-' }}</td>
                                            <td class="px-3 py-2 text-slate-700">{{ $movement->movement_type }}</td>
                                            <td class="px-3 py-2 text-right font-semibold text-slate-900">{{ $movement->quantity }}</td>
                                            <td class="px-3 py-2 text-right text-slate-700">{{ $movement->balance_after ?? '-' }}</td>
                                            <td class="px-3 py-2 text-slate-700">
                                                <div>{{ $movement->user?->name ?? __('Sistema') }}</div>
                                                @if ($movement->notes)
                                                    <div class="mt-1 text-xs text-slate-500">{{ $movement->notes }}</div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
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

    <livewire:pages.app.director.training.deliver-material-modal :training-id="$training->id"
        wire:key="deliver-material-modal-training-view-{{ $training->id }}" />
</div>
