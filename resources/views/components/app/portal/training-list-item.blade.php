@props([
    'training',
    'showStatus' => true,
])

@php
    $statusTone = match ($training['status']) {
        'em andamento', 'em_analise' => 'bg-sky-100 text-sky-800',
        'pendente' => 'bg-amber-100 text-amber-800',
        'concluido' => 'bg-emerald-100 text-emerald-800',
        default => 'bg-neutral-100 text-neutral-700',
    };
@endphp

<a href="{{ $training['detail_route'] }}"
    class="group flex flex-col gap-4 rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm transition hover:border-sky-300 hover:bg-sky-50/50">
    <div class="flex items-start justify-between gap-3">
        <div class="flex flex-col gap-2">
            <div class="flex flex-wrap gap-2">
                @foreach ($training['context_badges'] ?? [] as $badge)
                    <span class="rounded-full bg-sky-100 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-sky-800">
                        {{ $badge }}
                    </span>
                @endforeach
            </div>

            <div class="flex flex-col gap-1">
                <h3 class="text-base font-semibold text-neutral-950">{{ $training['title'] }}</h3>
                <p class="text-sm text-neutral-600">{{ $training['church_name'] }}</p>
            </div>
        </div>

        @if ($showStatus)
            <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] {{ $statusTone }}">
                {{ $training['status'] }}
            </span>
        @endif
    </div>

    <div class="grid gap-3 text-sm text-neutral-600 sm:grid-cols-2">
        <div class="flex flex-col gap-1">
            <span class="text-xs font-semibold uppercase tracking-[0.18em] text-neutral-400">{{ __('Agenda') }}</span>
            <span>{{ $training['schedule_summary'] }}</span>
        </div>
        <div class="flex flex-col gap-1">
            <span class="text-xs font-semibold uppercase tracking-[0.18em] text-neutral-400">{{ __('Local') }}</span>
            <span>{{ $training['location'] }}</span>
        </div>
    </div>

    <div class="flex flex-wrap gap-2 text-xs font-medium">
        @if ($training['payment_confirmed'])
            <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-emerald-800">{{ __('Pagamento confirmado') }}</span>
        @elseif ($training['receipt_in_review'])
            <span class="rounded-full bg-sky-100 px-2.5 py-1 text-sky-800">{{ __('Comprovante em analise') }}</span>
        @elseif ($training['receipt_pending'])
            <span class="rounded-full bg-amber-100 px-2.5 py-1 text-amber-800">{{ __('Comprovante pendente') }}</span>
        @elseif (! $training['payment_required'])
            <span class="rounded-full bg-neutral-100 px-2.5 py-1 text-neutral-700">{{ __('Sem pagamento') }}</span>
        @endif

        @if ($training['accredited'])
            <span class="rounded-full bg-neutral-100 px-2.5 py-1 text-neutral-700">{{ __('Credenciado') }}</span>
        @endif

        @if ($training['kit'])
            <span class="rounded-full bg-neutral-100 px-2.5 py-1 text-neutral-700">{{ __('Kit entregue') }}</span>
        @endif
    </div>

    <span class="text-sm font-semibold text-sky-800 transition group-hover:text-sky-900">
        {{ __('Abrir detalhes') }}
    </span>
</a>
