<div class="space-y-6">
    <section class="rounded-2xl border border-[color:var(--ee-app-border)] bg-white p-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <flux:heading size="sm" level="2">{{ __('Sessão STP') }}</flux:heading>
                <flux:text class="text-sm text-[color:var(--ee-app-muted)]">
                    {{ $session->label ?: __('Sessão :number', ['number' => $session->sequence]) }} ·
                    {{ $session->starts_at?->format('d/m/Y H:i') ?? __('Horário a definir') }}
                </flux:text>
            </div>
        </div>
    </section>

    <section class="space-y-4">
        @foreach ($session->teams as $team)
            <div class="rounded-2xl border border-[color:var(--ee-app-border)] bg-white p-6"
                wire:key="mentor-ojt-team-{{ $team->id }}">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <div class="text-sm font-semibold text-heading">
                            {{ $team->name ?: __('Equipe :number', ['number' => $team->position + 1]) }}
                        </div>
                        <div class="text-xs text-[color:var(--ee-app-muted)]">
                            {{ __('Mentor') }}: {{ $team->mentor?->name ?? __('Mentor') }}
                        </div>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                        {{ $team->approaches->count() }} {{ __('abordagens') }}
                    </span>
                </div>
                <div class="mt-3 flex flex-wrap gap-2 text-xs">
                    @foreach ($team->students as $student)
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-700"
                            wire:key="mentor-team-student-{{ $team->id }}-{{ $student->id }}">
                            {{ $student->name }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endforeach
    </section>
</div>
