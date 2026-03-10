<div class="space-y-6">
    <section class="rounded-2xl border border-[color:var(--ee-app-border)] bg-white">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-[color:var(--ee-app-muted)]">
                    <tr class="border-b border-[color:var(--ee-app-border)]">
                        <th class="px-4 py-3">{{ __('Training') }}</th>
                        <th class="px-4 py-3">{{ __('Sessão') }}</th>
                        <th class="px-4 py-3">{{ __('Início') }}</th>
                        <th class="px-4 py-3">{{ __('Teams') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[color:var(--ee-app-border)]">
                    @forelse ($sessions as $session)
                        <tr wire:key="mentor-ojt-session-{{ $session->id }}">
                            <td class="px-4 py-3">
                                <div class="font-semibold text-slate-900">{{ $session->training?->course?->name ?? __('Treinamento') }}</div>
                                <div class="text-xs text-slate-500">{{ $session->training?->church?->name ?? __('Igreja não informada') }}</div>
                            </td>
                            <td class="px-4 py-3">{{ $session->label ?: __('Sessão :number', ['number' => $session->sequence]) }}</td>
                            <td class="px-4 py-3">{{ $session->starts_at?->format('d/m/Y H:i') ?? __('A definir') }}</td>
                            <td class="px-4 py-3">{{ $session->teams->count() }}</td>
                            <td class="px-4 py-3 text-right">
                                <flux:button size="sm" variant="outline"
                                    :href="route('app.mentor.ojt.sessions.show', $session)">
                                    {{ __('Ver') }}
                                </flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-sm text-[color:var(--ee-app-muted)]">
                                {{ __('Nenhuma sessão STP/OJT vinculada ao seu perfil.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
