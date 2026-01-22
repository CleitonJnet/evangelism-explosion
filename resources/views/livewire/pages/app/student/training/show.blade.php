@php
    $user = auth()->user();
@endphp

<div class="flex flex-col gap-6">
    <div class="rounded-2xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
        <div class="flex flex-col gap-2">
            <div class="text-sm font-semibold text-neutral-500">Bem-vindo(a)</div>
            <div class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">{{ $user?->name }}</div>
        </div>
        <div class="mt-4 grid gap-3 text-sm text-neutral-700 dark:text-neutral-200 sm:grid-cols-2">
            <div class="flex flex-col gap-1">
                <span class="text-xs font-semibold uppercase text-neutral-500">E-mail</span>
                <span>{{ $user?->email }}</span>
            </div>
            <div class="flex flex-col gap-1">
                <span class="text-xs font-semibold uppercase text-neutral-500">Telefone</span>
                <span>{{ $user?->phone ?? 'Nao informado' }}</span>
            </div>
            <div class="flex flex-col gap-1">
                <span class="text-xs font-semibold uppercase text-neutral-500">Nascimento</span>
                <span>{{ $user?->birthdate ?? 'Nao informado' }}</span>
            </div>
            <div class="flex flex-col gap-1">
                <span class="text-xs font-semibold uppercase text-neutral-500">Genero</span>
                <span>{{ $user?->gender ?? 'Nao informado' }}</span>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
        <div class="flex flex-col gap-2">
            <span class="text-sm font-semibold text-neutral-500">Treinamento</span>
            <div class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">
                {{ $training->course?->type }}: {{ $training->course?->name }}
            </div>
            @if ($training->course?->slogan)
                <p class="text-sm text-neutral-600 dark:text-neutral-300">{{ $training->course?->slogan }}</p>
            @endif
        </div>

        <div class="mt-5 flex flex-wrap gap-3 text-sm">
            <span
                class="inline-flex items-center gap-1 rounded-full border border-neutral-200 bg-neutral-50 px-3 py-1 text-neutral-700 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-200">
                Carga horaria: <strong class="text-neutral-900 dark:text-neutral-100">{{ $workloadDuration ?? '00h' }}</strong>
            </span>
            <span
                class="inline-flex items-center gap-1 rounded-full border border-neutral-200 bg-neutral-50 px-3 py-1 text-neutral-700 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-200">
                Investimento: <strong class="text-neutral-900 dark:text-neutral-100">{{ $training->payment ?? 'Gratuito' }}</strong>
            </span>
        </div>

        <div class="mt-6 flex flex-col gap-3">
            @foreach ($training->eventDates as $dateEvent)
                <div wire:key="date-{{ $dateEvent->id }}" class="rounded-xl border border-neutral-200 bg-neutral-50 p-4 text-sm text-neutral-700 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-200">
                    <span class="font-semibold text-neutral-900 dark:text-neutral-100">
                        {{ \Illuminate\Support\Str::ucfirst(\Carbon\Carbon::parse($dateEvent->date)->locale('pt_BR')->isoFormat('dddd')) }}
                        - {{ date('d/m', strtotime($dateEvent->date)) }}
                    </span>
                    <span class="text-neutral-600 dark:text-neutral-300">
                        das {{ date('H:i', strtotime($dateEvent->start_time)) }} as {{ date('H:i', strtotime($dateEvent->end_time)) }}
                    </span>
                </div>
            @endforeach
        </div>

        <div class="mt-6 grid gap-4 text-sm text-neutral-700 dark:text-neutral-200 md:grid-cols-2">
            <div class="rounded-xl border border-neutral-200 bg-neutral-50 p-4 dark:border-neutral-700 dark:bg-neutral-800">
                <div class="text-xs font-semibold uppercase text-neutral-500">Local</div>
                <div class="mt-2 font-semibold text-neutral-900 dark:text-neutral-100">{{ $training->church?->name }}</div>
                <div class="mt-1">{{ $churchAddress ?: 'Endereco nao informado' }}</div>
            </div>
            <div class="rounded-xl border border-neutral-200 bg-neutral-50 p-4 dark:border-neutral-700 dark:bg-neutral-800">
                <div class="text-xs font-semibold uppercase text-neutral-500">Contato</div>
                <div class="mt-2 font-semibold text-neutral-900 dark:text-neutral-100">{{ $training->coordinator }}</div>
                <div class="mt-1">{{ $training->phone }}</div>
                <div>{{ $training->email }}</div>
            </div>
        </div>
    </div>
</div>
