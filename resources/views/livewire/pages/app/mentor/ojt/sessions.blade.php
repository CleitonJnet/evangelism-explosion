<div class="space-y-6">
    <section class="rounded-2xl border border-[color:var(--ee-app-border)] bg-white">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-[color:var(--ee-app-muted)]">
                    <tr class="border-b border-[color:var(--ee-app-border)]">
                        <th class="px-4 py-3">{{ __('Training') }}</th>
                        <th class="px-4 py-3">{{ __('Week') }}</th>
                        <th class="px-4 py-3">{{ __('Date') }}</th>
                        <th class="px-4 py-3">{{ __('Teams') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[color:var(--ee-app-border)]">
                    @forelse ($sessions as $session)
                        <tr wire:key="mentor-ojt-session-{{ $session->id }}">
                            <td class="px-4 py-3">{{ $session->training?->course?->name ?? __('Training') }}</td>
                            <td class="px-4 py-3">{{ $session->week_number }}</td>
                            <td class="px-4 py-3">{{ $session->date?->format('Y-m-d') }}</td>
                            <td class="px-4 py-3">{{ $session->teams->count() }}</td>
                            <td class="px-4 py-3 text-right">
                                <flux:button size="sm" variant="outline"
                                    :href="route('app.mentor.ojt.sessions.show', $session)">
                                    {{ __('View') }}
                                </flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-sm text-[color:var(--ee-app-muted)]">
                                {{ __('No OJT sessions assigned yet.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
