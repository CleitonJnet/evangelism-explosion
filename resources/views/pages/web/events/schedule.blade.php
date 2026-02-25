@php
    $title = 'Programação do Evento';
    $description = 'Programação pública do treinamento com horários por dia e turno.';
    $keywords = 'programação, evento, treinamento, horários';
    $ogImage = asset('images/leadership-meeting.webp');
    $hasMultipleDays = count($scheduleDays) > 1;
@endphp

<x-layouts.guest :title="$title" :description="$description" :keywords="$keywords" :ogImage="$ogImage">
    <x-web.header :title="$title" subtitle="Confira os horários oficiais deste treinamento." :cover="asset('images/leadership-meeting.webp')" />

    <section class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="space-y-1">
                    <h1 class="text-2xl font-black text-slate-900 sm:text-3xl">Programação do Evento</h1>
                    <p class="text-sm text-slate-600">{{ $training->course?->type }}: {{ $training->course?->name }}</p>
                    <p class="text-sm text-slate-600">
                        {{ $training->church?->name ?? 'Local a confirmar' }}
                        @if ($training->teacher)
                            | Professor: {{ $training->teacher->name }}
                        @endif
                    </p>
                    <p class="text-xs uppercase tracking-wide text-slate-500">Datas: {{ $datesSummary }}</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <x-src.btn-silver label="Voltar ao evento" :route="route('web.event.details', ['id' => $training->id])" />
                    <x-src.btn-silver label="Baixar PDF" :route="route('web.event.schedule.pdf', $training, false)" />
                </div>
            </div>
        </div>

        @if (count($scheduleDays) === 0)
            <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-6 text-center text-slate-600">
                Programação ainda não publicada.
            </div>
        @else
            <div class="mt-6 space-y-6">
                @foreach ($scheduleDays as $day)
                    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
                        @if ($hasMultipleDays)
                            <div class="rounded-t-3xl bg-sky-950 px-5 py-3">
                                <h2 class="text-sm font-black tracking-[0.08em] text-white sm:text-base">
                                    {{ $day['dayLabel'] }}</h2>
                            </div>
                        @endif

                        <div class="space-y-4 p-4 sm:p-6">
                            @foreach ($day['groups'] as $group)
                                <div class="overflow-hidden rounded-2xl border border-slate-200">
                                    <div class="bg-sky-700 px-4 py-2">
                                        @php
                                            $turnLabel = match ($group['turn']) {
                                                'MANHA' => 'MANHÃ',
                                                'TARDE' => 'TARDE',
                                                'NOITE' => 'NOITE',
                                                default => 'TURNO',
                                            };
                                        @endphp
                                        <h3 class="text-sm font-bold uppercase tracking-wide text-white">
                                            {{ $turnLabel }}
                                        </h3>
                                    </div>

                                    <table class="w-full border-collapse">
                                        <thead>
                                            <tr
                                                class="bg-slate-100 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-600">
                                                <th class="w-32 px-4 py-2">Horário</th>
                                                <th class="px-4 py-2">Sessão</th>
                                                <th class="w-20 px-4 py-2 text-right">Duração</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-200">
                                            @foreach ($group['items'] as $item)
                                                <tr class="align-top">
                                                    <td class="px-4 py-2 text-sm font-bold text-slate-900">
                                                        {{ $item['timeRange'] }}</td>
                                                    <td class="px-4 py-2">
                                                        <p class="text-sm font-semibold text-slate-900">
                                                            {{ $item['title'] }}</p>
                                                        @if (!empty($item['devotional']))
                                                            <p class="mt-0.5 text-xs text-slate-500">Devocional:
                                                                {{ $item['devotional'] }}</p>
                                                        @endif
                                                    </td>
                                                    <td
                                                        class="px-4 py-2 text-right text-sm font-semibold text-slate-700 align-middle">
                                                        {{ $item['duration'] }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</x-layouts.guest>
