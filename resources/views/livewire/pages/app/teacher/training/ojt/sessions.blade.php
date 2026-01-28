<div class="space-y-6">
    <section class="rounded-2xl border border-[color:var(--ee-app-border)] bg-white p-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <flux:heading size="sm" level="2">{{ __('OJT Sessions') }}</flux:heading>
                <flux:text class="text-sm text-[color:var(--ee-app-muted)]">
                    {{ __('Expected sessions') }}: {{ $expectedCount }}
                </flux:text>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <form method="POST" action="{{ route('app.teacher.trainings.ojt.sessions.generate', $training) }}">
                    @csrf
                    <flux:button variant="primary" type="submit" icon="sparkles">
                        {{ __('Generate sessions') }}
                    </flux:button>
                </form>
                <flux:button variant="outline" :href="route('app.teacher.trainings.ojt.sessions.create', $training)">
                    {{ __('New session') }}
                </flux:button>
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-[color:var(--ee-app-border)] bg-white">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-[color:var(--ee-app-muted)]">
                    <tr class="border-b border-[color:var(--ee-app-border)]">
                        <th class="px-4 py-3">{{ __('Week') }}</th>
                        <th class="px-4 py-3">{{ __('Date') }}</th>
                        <th class="px-4 py-3">{{ __('Time') }}</th>
                        <th class="px-4 py-3">{{ __('Teams') }}</th>
                        <th class="px-4 py-3">{{ __('Status') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[color:var(--ee-app-border)]">
                    @forelse ($sessions as $session)
                        <tr wire:key="ojt-session-{{ $session->id }}">
                            <td class="px-4 py-3">{{ $session->week_number }}</td>
                            <td class="px-4 py-3">{{ $session->date?->format('Y-m-d') }}</td>
                            <td class="px-4 py-3">
                                {{ $session->starts_at ?? '-' }} - {{ $session->ends_at ?? '-' }}
                            </td>
                            <td class="px-4 py-3">{{ $session->teams_count }}</td>
                            <td class="px-4 py-3">{{ strtoupper($session->status ?? 'planned') }}</td>
                            <td class="px-4 py-3 text-right">
                                <flux:button size="sm" variant="outline"
                                    :href="route('app.teacher.trainings.ojt.sessions.edit', ['training' => $training, 'session' => $session])">
                                    {{ __('Edit') }}
                                </flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-sm text-[color:var(--ee-app-muted)]">
                                {{ __('No OJT sessions yet.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
