<div class="space-y-6">
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Sessões') }}</div>
            <div class="mt-2 text-3xl font-bold text-slate-900">{{ $summary['sessions_count'] }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Equipes') }}</div>
            <div class="mt-2 text-3xl font-bold text-slate-900">{{ $summary['teams_count'] }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Abordagens') }}</div>
            <div class="mt-2 text-3xl font-bold text-slate-900">{{ $summary['approaches_summary']['total'] }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Decisões') }}</div>
            <div class="mt-2 text-3xl font-bold text-slate-900">{{ $summary['approaches_summary']['decisoes'] }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Acompanhamentos') }}</div>
            <div class="mt-2 text-3xl font-bold text-slate-900">{{ $summary['approaches_summary']['acompanhamentos'] }}</div>
        </div>
    </section>

    <section class="space-y-4">
        @forelse ($sessions as $session)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">
                            {{ $session->label ?: __('Sessão :number', ['number' => $session->sequence]) }}
                        </h3>
                        <p class="text-sm text-slate-600">
                            {{ $session->starts_at?->format('d/m/Y H:i') ?? __('Horário a definir') }}
                        </p>
                    </div>
                    <flux:button size="sm" variant="outline" :href="route('app.mentor.ojt.sessions.show', $session)">
                        {{ __('Abrir sessão') }}
                    </flux:button>
                </div>

                <div class="mt-4 grid gap-4 xl:grid-cols-2">
                    @foreach ($session->teams as $team)
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <div class="flex items-center justify-between gap-2">
                                <div>
                                    <div class="font-semibold text-slate-900">{{ $team->name ?: __('Equipe :number', ['number' => $team->position + 1]) }}</div>
                                    <div class="text-xs text-slate-500">{{ __('Mentor') }}: {{ $team->mentor?->name ?? __('Não informado') }}</div>
                                </div>
                                <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-700">
                                    {{ $team->approaches->count() }} {{ __('abordagens') }}
                                </span>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-2">
                                @forelse ($team->students as $student)
                                    <span class="rounded-full bg-white px-3 py-1 text-xs text-slate-700">{{ $student->name }}</span>
                                @empty
                                    <span class="text-xs text-slate-500">{{ __('Sem alunos vinculados a esta equipe.') }}</span>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-sm text-slate-600">
                {{ __('Nenhuma sessão STP/OJT vinculada à sua mentoria neste treinamento.') }}
            </div>
        @endforelse
    </section>
</div>
