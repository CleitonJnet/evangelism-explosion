<div class="space-y-4">
    @forelse ($trainings as $training)
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="space-y-2">
                    <div class="text-lg font-semibold text-slate-900">
                        {{ trim(($training->course?->type ?? '').' '.($training->course?->name ?? '')) }}
                    </div>
                    <div class="text-sm text-slate-600">{{ $training->church?->name ?? __('Igreja não informada') }}</div>
                    <div class="text-sm text-slate-500">
                        {{ $training->city ?: __('Cidade não informada') }}{{ $training->state ? ', '.$training->state : '' }}
                    </div>
                    <div class="text-sm text-slate-500">{{ __('Professor responsável') }}: {{ $training->teacher?->name ?? __('Não informado') }}</div>
                    <div class="flex flex-wrap gap-2 pt-1">
                        @forelse ($training->eventDates as $eventDate)
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">
                                {{ $eventDate->date ? \Illuminate\Support\Carbon::parse($eventDate->date)->format('d/m/Y') : __('Data a definir') }}
                            </span>
                        @empty
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">
                                {{ __('Sem datas cadastradas') }}
                            </span>
                        @endforelse
                    </div>
                </div>

                <div class="flex shrink-0 flex-wrap gap-2">
                    <flux:button variant="outline" :href="route('app.mentor.trainings.show', $training)">{{ __('Resumo') }}</flux:button>
                    <flux:button variant="ghost" :href="route('app.mentor.trainings.ojt', $training)">{{ __('STP/OJT') }}</flux:button>
                </div>
            </div>
        </article>
    @empty
        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-sm text-slate-600">
            {{ __('Nenhum treinamento disponível para o seu vínculo de mentor.') }}
        </div>
    @endforelse
</div>
