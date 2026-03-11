<div class="space-y-6">
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Treinamentos') }}</div>
            <div class="mt-2 text-3xl font-bold text-slate-900">{{ $dashboard['trainings_count'] }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Equipes sob mentoria') }}
            </div>
            <div class="mt-2 text-3xl font-bold text-slate-900">{{ $dashboard['teams_count'] }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Sessões concluídas') }}
            </div>
            <div class="mt-2 text-3xl font-bold text-slate-900">{{ $dashboard['completed_sessions_count'] }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Abordagens com decisão') }}
            </div>
            <div class="mt-2 text-3xl font-bold text-slate-900">{{ $dashboard['approaches_summary']['decisoes'] }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Acompanhamentos') }}</div>
            <div class="mt-2 text-3xl font-bold text-slate-900">
                {{ $dashboard['approaches_summary']['acompanhamentos'] }}</div>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.1fr,0.9fr]">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('Próximas sessões STP') }}</h2>
                    <p class="text-sm text-slate-600">{{ __('Somente sessões vinculadas às suas equipes.') }}</p>
                </div>
                <flux:button size="sm" variant="outline" :href="route('app.mentor.ojt.sessions.index')">
                    {{ __('Ver todas') }}</flux:button>
            </div>

            <div class="mt-4 space-y-3">
                @forelse ($dashboard['next_sessions'] as $session)
                    <a href="{{ route('app.mentor.ojt.sessions.show', $session) }}"
                        class="block rounded-xl border border-slate-200 bg-slate-50 p-4 transition hover:border-amber-300 hover:bg-amber-50/60">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="font-semibold text-slate-900">
                                    {{ $session->label ?: __('Sessão :number', ['number' => $session->sequence]) }}
                                </div>
                                <div class="text-sm text-slate-600">
                                    {{ $session->training?->course?->name ?? __('Treinamento') }}</div>
                                <div class="text-xs text-slate-500">
                                    {{ $session->training?->church?->name ?? __('Igreja não informada') }}</div>
                            </div>
                            <div class="text-right text-xs text-slate-500">
                                <div>{{ $session->starts_at?->format('d/m/Y H:i') ?? __('A definir') }}</div>
                                <div>{{ __('Equipes') }}: {{ $session->teams->count() }}</div>
                            </div>
                        </div>
                    </a>
                @empty
                    <div
                        class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-600">
                        {{ __('Nenhuma sessão STP vinculada ao seu perfil no momento.') }}
                    </div>
                @endforelse
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Resumo ministerial da prática') }}</h2>
            <p class="mt-1 text-sm text-slate-600">
                {{ __('Indicadores agregados apenas das equipes em que você atua como mentor.') }}</p>

            <div class="mt-4 grid gap-3">
                <div class="rounded-xl bg-slate-50 p-4">
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        {{ __('Abordagens totais') }}</div>
                    <div class="mt-1 text-2xl font-bold text-slate-900">{{ $dashboard['approaches_summary']['total'] }}
                    </div>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-xl bg-slate-50 p-4">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                            {{ __('Concluídas') }}</div>
                        <div class="mt-1 text-xl font-bold text-slate-900">
                            {{ $dashboard['approaches_summary']['concluidas'] }}</div>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-4">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Revisadas') }}
                        </div>
                        <div class="mt-1 text-xl font-bold text-slate-900">
                            {{ $dashboard['approaches_summary']['revisadas'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Treinamentos sob mentoria') }}</h2>
                <p class="text-sm text-slate-600">
                    {{ __('Acesso rápido para o resumo do evento e acompanhamento STP.') }}</p>
            </div>
            <flux:button size="sm" variant="outline" :href="route('app.mentor.trainings.index')">
                {{ __('Abrir lista') }}</flux:button>
        </div>

        <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($trainings as $training)
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <div class="text-sm font-semibold text-slate-900">
                        {{ trim(($training->course?->type ?? '') . ' ' . ($training->course?->name ?? '')) }}</div>
                    <div class="mt-1 text-sm text-slate-600">
                        {{ $training->church?->name ?? __('Igreja não informada') }}</div>
                    <div class="mt-1 text-xs text-slate-500">
                        {{ $training->city }}{{ $training->state ? ', ' . $training->state : '' }}</div>
                    <div class="mt-3 flex gap-2">
                        <flux:button size="sm" variant="outline"
                            :href="route('app.mentor.trainings.show', $training)">{{ __('Resumo') }}</flux:button>
                        <flux:button size="sm" variant="ghost"
                            :href="route('app.mentor.trainings.ojt', $training)">{{ __('STP') }}</flux:button>
                    </div>
                </div>
            @empty
                <div
                    class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-600 md:col-span-2 xl:col-span-3">
                    {{ __('Nenhum treinamento vinculado como mentor foi encontrado.') }}
                </div>
            @endforelse
        </div>
    </section>
</div>
