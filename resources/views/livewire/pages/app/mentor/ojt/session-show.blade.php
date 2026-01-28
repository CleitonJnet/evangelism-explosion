<div class="space-y-6">
    <section class="rounded-2xl border border-[color:var(--ee-app-border)] bg-white p-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <flux:heading size="sm" level="2">{{ __('OJT Session') }}</flux:heading>
                <flux:text class="text-sm text-[color:var(--ee-app-muted)]">
                    {{ __('Week') }} {{ $session->week_number }} · {{ $session->date?->format('Y-m-d') }}
                </flux:text>
            </div>
        </div>
    </section>

    <section class="space-y-4">
        @foreach ($session->teams as $team)
            <div class="rounded-2xl border border-[color:var(--ee-app-border)] bg-white p-6" wire:key="mentor-ojt-team-{{ $team->id }}">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <div class="text-sm font-semibold text-heading">
                            {{ __('Team') }} {{ $team->team_number }}
                        </div>
                        <div class="text-xs text-[color:var(--ee-app-muted)]">
                            {{ __('Mentor') }}: {{ $team->mentor?->name ?? __('Mentor') }}
                        </div>
                    </div>
                    <flux:button size="sm" variant="outline"
                        :href="route('app.mentor.ojt.teams.report.create', $team)">
                        {{ __('Report') }}
                    </flux:button>
                </div>
                <div class="mt-3 flex flex-wrap gap-2 text-xs">
                    @foreach ($team->trainees as $trainee)
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-700" wire:key="mentor-team-trainee-{{ $team->id }}-{{ $trainee->id }}">
                            {{ $trainee->trainee?->name ?? __('Trainee') }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endforeach
    </section>
</div>
