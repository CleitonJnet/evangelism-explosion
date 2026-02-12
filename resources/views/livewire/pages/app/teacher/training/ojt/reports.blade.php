<div class="space-y-6">
    <section class="rounded-2xl border border-[color:var(--ee-app-border)] bg-white p-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <flux:heading size="sm" level="2">{{ __('OJT Reports') }}</flux:heading>
                <flux:text class="text-sm text-[color:var(--ee-app-muted)]">
                    {{ __('Mentors submit reports after each session.') }}
                </flux:text>
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-[color:var(--ee-app-border)] bg-white">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-[color:var(--ee-app-muted)]">
                    <tr class="border-b border-[color:var(--ee-app-border)]">
                        <th class="px-4 py-3">{{ __('Session') }}</th>
                        <th class="px-4 py-3">{{ __('Mentor') }}</th>
                        <th class="px-4 py-3">{{ __('Submitted') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[color:var(--ee-app-border)]">
                    @forelse ($reports as $report)
                        <tr wire:key="ojt-report-{{ $report->id }}">
                            <td class="px-4 py-3">
                                {{ __('Week') }} {{ $report->team?->session?->week_number }}
                            </td>
                            <td class="px-4 py-3">
                                {{ $report->team?->mentor?->name ?? __('Mentor') }}
                            </td>
                            <td class="px-4 py-3">
                                {{ $report->submitted_at ? $report->submitted_at->format('Y-m-d H:i') : __('Pending') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <flux:button size="sm" variant="outline"
                                    :href="route('app.teacher.trainings.ojt.reports.show', ['training' => $training, 'report' => $report])">
                                    {{ __('View') }}
                                </flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-sm text-[color:var(--ee-app-muted)]">
                                {{ __('No reports submitted yet.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
