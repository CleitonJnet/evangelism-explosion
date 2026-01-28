<div class="space-y-6">
    <section class="rounded-2xl border border-[color:var(--ee-app-border)] bg-white p-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <flux:heading size="sm" level="2">{{ __('OJT Statistics') }}</flux:heading>
                <flux:text class="text-sm text-[color:var(--ee-app-muted)]">
                    {{ __('Summary across all submitted OJT reports.') }}
                </flux:text>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <flux:button size="sm" variant="outline"
                    :href="route('app.teacher.trainings.ojt.stats.summary', $training)">
                    {{ __('Summary') }}
                </flux:button>
                <flux:button size="sm" variant="outline"
                    :href="route('app.teacher.trainings.ojt.stats.public-report', $training)">
                    {{ __('Public report') }}
                </flux:button>
            </div>
        </div>
    </section>

    @if ($mode === 'summary')
        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-2xl border border-[color:var(--ee-app-border)] bg-white p-4">
                <div class="text-xs text-[color:var(--ee-app-muted)]">{{ __('Gospel presentations') }}</div>
                <div class="text-xl font-semibold text-heading">{{ $gospelPresentations }}</div>
            </div>
            <div class="rounded-2xl border border-[color:var(--ee-app-border)] bg-white p-4">
                <div class="text-xs text-[color:var(--ee-app-muted)]">{{ __('Listeners') }}</div>
                <div class="text-xl font-semibold text-heading">{{ $listenersCount }}</div>
            </div>
            <div class="rounded-2xl border border-[color:var(--ee-app-border)] bg-white p-4">
                <div class="text-xs text-[color:var(--ee-app-muted)]">{{ __('Decisions') }}</div>
                <div class="text-xl font-semibold text-heading">{{ $resultsDecisions }}</div>
            </div>
            <div class="rounded-2xl border border-[color:var(--ee-app-border)] bg-white p-4">
                <div class="text-xs text-[color:var(--ee-app-muted)]">{{ __('Interested') }}</div>
                <div class="text-xl font-semibold text-heading">{{ $resultsInterested }}</div>
            </div>
            <div class="rounded-2xl border border-[color:var(--ee-app-border)] bg-white p-4">
                <div class="text-xs text-[color:var(--ee-app-muted)]">{{ __('Rejection') }}</div>
                <div class="text-xl font-semibold text-heading">{{ $resultsRejection }}</div>
            </div>
            <div class="rounded-2xl border border-[color:var(--ee-app-border)] bg-white p-4">
                <div class="text-xs text-[color:var(--ee-app-muted)]">{{ __('Assurance') }}</div>
                <div class="text-xl font-semibold text-heading">{{ $resultsAssurance }}</div>
            </div>
        </section>
    @endif

    @if ($mode === 'public')
        <section class="rounded-2xl border border-[color:var(--ee-app-border)] bg-white p-6">
            <flux:heading size="xs" level="3">{{ __('Public reports') }}</flux:heading>
            <div class="mt-4 space-y-3">
                @forelse ($publicReports as $index => $report)
                    <div class="rounded-xl border border-[color:var(--ee-app-border)] p-4" wire:key="ojt-public-report-{{ $index }}">
                        <pre class="whitespace-pre-wrap text-sm text-slate-700">{{ json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                @empty
                    <div class="text-sm text-[color:var(--ee-app-muted)]">
                        {{ __('No public reports available.') }}
                    </div>
                @endforelse
            </div>
        </section>
    @endif
</div>
