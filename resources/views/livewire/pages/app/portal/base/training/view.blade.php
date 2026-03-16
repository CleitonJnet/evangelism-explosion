@php
    use App\Helpers\AddressHelper;
    use App\Services\Training\TestimonySanitizer;

    $course = $training->course;
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
    $formattedNotes = TestimonySanitizer::sanitize($training->notes);
@endphp

<div class="space-y-4">
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
        <div class="rounded-2xl border border-sky-950/45 bg-linear-to-br from-slate-100 via-white to-slate-200 px-4 py-3">
            <div class="text-xs text-slate-500">{{ __('Inscricoes') }}</div>
            <div class="text-3xl font-bold text-slate-900">{{ $totalRegistrations }}</div>
        </div>
        <div class="rounded-2xl border border-sky-950/45 bg-linear-to-br from-slate-100 via-white to-slate-200 px-4 py-3">
            <div class="text-xs text-slate-500">{{ __('Igrejas') }}</div>
            <div class="text-3xl font-bold text-slate-900">{{ $totalParticipatingChurches }}</div>
        </div>
        <div class="rounded-2xl border border-sky-950/45 bg-linear-to-br from-slate-100 via-white to-slate-200 px-4 py-3">
            <div class="text-xs text-slate-500">{{ __('Pastores') }}</div>
            <div class="text-3xl font-bold text-slate-900">{{ $totalPastors }}</div>
        </div>
        <div class="rounded-2xl border border-sky-950/45 bg-linear-to-br from-slate-100 via-white to-slate-200 px-4 py-3">
            <div class="text-xs text-slate-500">{{ __('Novas igrejas') }}</div>
            <div class="text-3xl font-bold text-slate-900">{{ $totalNewChurches }}</div>
        </div>
        <div class="rounded-2xl border border-sky-950/45 bg-linear-to-br from-slate-100 via-white to-slate-200 px-4 py-3">
            <div class="text-xs text-slate-500">{{ __('Decisoes') }}</div>
            <div class="text-3xl font-bold text-slate-900">{{ $totalDecisions }}</div>
        </div>
    </section>

    <section class="grid gap-4 xl:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
        <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-neutral-950">{{ __('Visao geral do evento') }}</h3>
                    <p class="text-sm text-neutral-600">{{ __('Resumo compartilhado do treinamento, reutilizado dentro do Portal Base com filtros de acesso.') }}</p>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4">
                        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-neutral-500">{{ __('Curso') }}</div>
                        <div class="mt-2 text-sm font-semibold text-neutral-950">{{ $course?->name ?? __('Treinamento') }}</div>
                        <div class="text-sm text-neutral-600">{{ $course?->type ?? __('Tipo nao informado') }}</div>
                    </div>
                    <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4">
                        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-neutral-500">{{ __('Professor titular') }}</div>
                        <div class="mt-2 text-sm font-semibold text-neutral-950">{{ $training->teacher?->name ?? __('Nao informado') }}</div>
                    </div>
                    <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4 md:col-span-2">
                        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-neutral-500">{{ __('Endereco do evento') }}</div>
                        <div class="mt-2 text-sm text-neutral-800">{{ $eventAddress !== '' ? $eventAddress : __('Nao informado') }}</div>
                    </div>
                </div>

                @if ($formattedNotes)
                    <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4">
                        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-neutral-500">{{ __('Relato do evento') }}</div>
                        <div class="prose prose-sm mt-3 max-w-none text-neutral-700">{!! $formattedNotes !!}</div>
                    </div>
                @endif
            </div>
        </div>

        <div class="space-y-4">
            <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                <div>
                    <h3 class="text-lg font-semibold text-neutral-950">{{ __('Datas e STP') }}</h3>
                    <p class="text-sm text-neutral-600">{{ __('Leitura executiva da agenda e da saida pratica.') }}</p>
                </div>

                <div class="mt-4 grid gap-3">
                    @forelse ($eventDates as $eventDate)
                        <div class="rounded-2xl border border-neutral-200 bg-neutral-50 px-4 py-3 text-sm text-neutral-700">
                            {{ \Carbon\Carbon::parse($eventDate->date)->format('d/m/Y') }}
                            @if ($eventDate->start_time && $eventDate->end_time)
                                <span class="text-neutral-500">· {{ substr((string) $eventDate->start_time, 0, 5) }} - {{ substr((string) $eventDate->end_time, 0, 5) }}</span>
                            @endif
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 p-4 text-sm text-neutral-600">
                            {{ __('Nenhuma data cadastrada para este evento.') }}
                        </div>
                    @endforelse
                </div>

                <div class="mt-4 grid gap-3 text-sm text-neutral-700">
                    <div class="flex items-center justify-between gap-3">
                        <span>{{ __('Sessoes concluidas / previstas') }}</span>
                        <span class="font-semibold text-neutral-950">{{ $resumoStp['sessoes_concluidas'] }} / {{ $resumoStp['sessoes_previstas'] }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span>{{ __('Pessoas que ouviram') }}</span>
                        <span class="font-semibold text-neutral-950">{{ $resumoStp['pessoas_ouviram'] }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span>{{ __('Decisoes') }}</span>
                        <span class="font-semibold text-neutral-950">{{ $resumoStp['decisao'] }}</span>
                    </div>
                </div>
            </div>

            @if ($capabilities['canSeeFinance'] ?? false)
                <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-neutral-950">{{ __('Resumo financeiro') }}</h3>
                    <div class="mt-4 grid gap-3 text-sm text-neutral-700">
                        <div class="flex items-center justify-between gap-3">
                            <span>{{ __('Pagantes confirmados') }}</span>
                            <span class="font-semibold text-neutral-950">{{ $paidStudentsCount }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>{{ __('Receita total') }}</span>
                            <span class="font-semibold text-neutral-950">{{ $totalReceivedFromRegistrations ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>{{ __('Repasse EE') }}</span>
                            <span class="font-semibold text-neutral-950">{{ $eeMinistryBalance ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
</div>
