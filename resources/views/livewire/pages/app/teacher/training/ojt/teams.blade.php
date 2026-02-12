<div class="space-y-6">
    <section class="rounded-2xl border border-[color:var(--ee-app-border)] bg-white p-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <flux:heading size="sm" level="2">{{ __('OJT Teams') }}</flux:heading>
                <flux:text class="text-sm text-[color:var(--ee-app-muted)]">
                    {{ __('Teams are generated as 1 mentor + 2 trainees.') }}
                </flux:text>
            </div>
            <form method="POST" action="{{ route('app.teacher.trainings.ojt.teams.generate', $training) }}">
                @csrf
                <flux:button variant="primary" type="submit" icon="sparkles">
                    {{ __('Generate teams') }}
                </flux:button>
            </form>
        </div>
    </section>

    <section class="space-y-6">
        @forelse ($sessions as $session)
            <div class="rounded-2xl border border-[color:var(--ee-app-border)] bg-white p-6" wire:key="ojt-team-session-{{ $session->id }}">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <flux:heading size="xs" level="3">{{ __('Week') }} {{ $session->week_number }}</flux:heading>
                        <flux:text class="text-sm text-[color:var(--ee-app-muted)]">
                            {{ $session->date?->format('Y-m-d') }}
                        </flux:text>
                    </div>
                </div>

                <div class="mt-4 space-y-3">
                    @forelse ($session->teams as $team)
                        <div class="rounded-xl border border-[color:var(--ee-app-border)] p-4" wire:key="ojt-team-{{ $team->id }}">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold text-heading">
                                        {{ __('Team') }} {{ $team->team_number }}
                                    </div>
                                    <div class="text-xs text-[color:var(--ee-app-muted)]">
                                        {{ __('Mentor') }}: {{ $team->mentor?->name ?? __('Unassigned') }}
                                    </div>
                                </div>
                                <flux:button size="sm" variant="outline" type="button">
                                    {{ __('Edit assignments') }}
                                </flux:button>
                            </div>
                            <div class="mt-3 flex flex-wrap gap-2 text-xs">
                                @foreach ($team->trainees as $trainee)
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-700" wire:key="ojt-team-trainee-{{ $team->id }}-{{ $trainee->id }}">
                                        {{ $trainee->trainee?->name ?? __('Trainee') }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-[color:var(--ee-app-border)] p-4 text-sm text-[color:var(--ee-app-muted)]">
                            {{ __('No teams generated for this session.') }}
                        </div>
                    @endforelse
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-[color:var(--ee-app-border)] bg-white p-6 text-sm text-[color:var(--ee-app-muted)]">
                {{ __('No OJT sessions available.') }}
            </div>
        @endforelse
    </section>
</div>
