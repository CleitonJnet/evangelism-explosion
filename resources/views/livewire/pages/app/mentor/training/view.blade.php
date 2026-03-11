<div class="space-y-6">
    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="space-y-2">
                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Curso') }}</div>
                <h2 class="text-2xl font-bold text-slate-900">
                    {{ trim(($training->course?->type ?? '') . ' ' . ($training->course?->name ?? '')) }}</h2>
                <div class="text-sm text-slate-600">{{ $training->church?->name ?? __('Igreja não informada') }}</div>
                <div class="text-sm text-slate-500">
                    {{ $training->city ?: __('Cidade não informada') }}{{ $training->state ? ', ' . $training->state : '' }}
                </div>
                <div class="text-sm text-slate-500">{{ __('Professor responsável') }}:
                    {{ $training->teacher?->name ?? __('Não informado') }}</div>
            </div>

            <div class="flex flex-wrap gap-2">
                <flux:button variant="outline" :href="route('app.mentor.trainings.ojt', $training)">
                    {{ __('Abrir STP') }}</flux:button>
                <flux:button variant="ghost" :href="route('app.mentor.ojt.sessions.index')">{{ __('Sessões') }}
                </flux:button>
            </div>
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Sessões vinculadas') }}
            </div>
            <div class="mt-2 text-3xl font-bold text-slate-900">{{ $summary['sessions_count'] }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Equipes sob mentoria') }}
            </div>
            <div class="mt-2 text-3xl font-bold text-slate-900">{{ $summary['teams_count'] }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                {{ __('Participantes nas equipes') }}</div>
            <div class="mt-2 text-3xl font-bold text-slate-900">{{ $summary['students_count'] }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                {{ __('Abordagens acompanhadas') }}</div>
            <div class="mt-2 text-3xl font-bold text-slate-900">{{ $summary['approaches_summary']['total'] }}</div>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold text-slate-900">{{ __('Datas do evento') }}</h3>
        <div class="mt-4 flex flex-wrap gap-2">
            @forelse ($training->eventDates as $eventDate)
                <span class="rounded-full bg-slate-100 px-3 py-1 text-sm text-slate-700">
                    {{ $eventDate->date ? \Illuminate\Support\Carbon::parse($eventDate->date)->format('d/m/Y') : __('Data a definir') }}
                    @if ($eventDate->start_time)
                        · {{ \Illuminate\Support\Carbon::parse($eventDate->start_time)->format('H:i') }}
                    @endif
                </span>
            @empty
                <span class="text-sm text-slate-600">{{ __('Nenhuma data cadastrada para este treinamento.') }}</span>
            @endforelse
        </div>
    </section>
</div>
