@php
    $course = $training->course;
    $ministry = $course?->ministry;
    $teacher = $training->teacher;
    $church = $training->church;
    $statusLabel = $training->status?->label() ?? __('Status não definido');
    $bannerUrl = $training->banner ? asset('storage/' . $training->banner) : null;
@endphp

<div class="flex flex-col gap-8">
    <section
        class="rounded-2xl border border-amber-300/20 bg-linear-to-br from-slate-100 via-white to-slate-200 p-6 shadow-lg">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b-2 border-slate-200/80 pb-2">
            <div class="flex flex-col gap-1">
                <h2 class="text-xl font-semibold text-slate-900" style="font-family: 'Cinzel', serif;">
                    {{ $course?->type ?? __('Treinamento') }}: <span
                        class="font-semibold">{{ $course?->name ?? __('Curso não definido') }}</span>
                </h2>
                <p class="text-sm text-slate-600">
                    {{ __('Detalhes completos do evento e do treinamento selecionado.') }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
                    {{ $statusLabel }}
                </span>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                    {{ __('Programação:') }} {{ $training->schedule_items_count }}
                </span>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                    {{ __('Datas:') }} {{ $eventDates->count() }}
                </span>
            </div>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-3">
            <div class="rounded-2xl border border-slate-200/80 bg-white/90 p-4">
                <h3 class="text-sm font-semibold text-slate-900 uppercase">{{ __('Curso') }}</h3>
                <div class="mt-3 flex flex-col gap-3 text-sm text-slate-700">
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500">{{ __('Tipo') }}</span>
                        <span class="font-semibold text-slate-900">{{ $course?->type ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500">{{ __('Ministério') }}</span>
                        <span class="font-semibold text-slate-900">{{ $ministry?->name ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500">{{ __('Sigla') }}</span>
                        <span class="font-semibold text-slate-900">{{ $course?->initials ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500">{{ __('Professor') }}</span>
                        <span class="font-semibold text-slate-900 text-right">
                            <div class="mt-1 text-slate-900">{{ $teacher?->name ?? __('Não informado') }}</div>
                            <div class="text-xs text-slate-500">{{ $teacher?->email ?? '' }}</div>
                        </span>
                    </div>
                </div>

                <x-src.line-theme class="my-4" />
                <div class="mt-3 flex flex-col gap-3 text-sm text-slate-700">

                    <div class="flex flex-col gap-3 text-sm text-slate-700">
                        <div>
                            <span class="text-slate-500">{{ __('Slogan') }}</span>
                            <p class="mt-1 text-slate-900">{{ $course?->slogan ?? __('Não informado') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500">{{ __('Certificado') }}</span>
                        <span class="font-semibold text-slate-900">{{ $course?->certificate ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500">{{ __('Cor temática do curso') }}</span>
                        <span class="font-semibold text-slate-900">{{ $course?->color ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white/90 p-4">
                <h3 class="text-sm font-semibold text-slate-900 uppercase">{{ __('Igreja Base') }}</h3>
                <div class="mt-3 flex flex-col gap-4 text-sm text-slate-700">
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500">{{ __('Líder da Clínica/Pastor') }}</span>
                        <span
                            class="font-semibold text-slate-900 text-right">{{ $church->pastor ?? __(key: 'Não informado') }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500">{{ __('Coordenador') }}</span>
                        <span
                            class="font-semibold text-slate-900">{{ $training->coordinator ?? __(key: 'Não informado') }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500">{{ __('Nome da Igreja') }}</span>
                        <span class="font-semibold text-slate-900">
                            <div class="mt-1 text-slate-900 text-right">{{ $church?->name ?? __('Não informado') }}
                            </div>
                            <div class="text-xs text-slate-500 text-right">
                                {{ $church?->city ? $church->city . ' / ' . $church->state : '' }}</div>
                        </span>
                    </div>
                </div>

                <x-src.line-theme class="my-4" />

                <div class="flex flex-col gap-3 text-sm text-slate-700">
                    <div>
                        <span class="text-slate-500">{{ __('Contato') }}</span>
                        <p class="mt-1 text-slate-900">{{ $training->phone ?? __('Não informado') }}</p>
                        <p class="text-xs text-slate-500">{{ $training->email ?? '' }}</p>
                    </div>
                    <div>
                        <span class="text-slate-500">{{ __('WhatsApp') }}</span>
                        <p class="mt-1 text-slate-900">{{ $training->gpwhatsapp ?? __('Não informado') }}</p>
                    </div>
                    <div>
                        <span class="text-slate-500">{{ __('URL') }}</span>
                        <p class="mt-1 text-slate-900">{{ $training->url ?? __('Não informado') }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white/90 p-4">
                <h3 class="text-sm font-semibold text-slate-900 uppercase">{{ __('Local & endereço') }}</h3>
                <div class="mt-3 flex flex-col gap-3 text-sm text-slate-700">
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500">{{ __('CEP') }}</span>
                        <span class="font-semibold text-slate-900">{{ $training->postal_code ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500">{{ __('Rua') }}</span>
                        <span class="font-semibold text-slate-900">{{ $training->street ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500">{{ __('Número') }}</span>
                        <span class="font-semibold text-slate-900">{{ $training->number ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500">{{ __('Complemento') }}</span>
                        <span class="font-semibold text-slate-900">{{ $training->complement ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500">{{ __('Bairro') }}</span>
                        <span class="font-semibold text-slate-900">{{ $training->district ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500">{{ __('Cidade') }}</span>
                        <span class="font-semibold text-slate-900">{{ $training->city ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500">{{ __('Estado') }}</span>
                        <span class="font-semibold text-slate-900">{{ $training->state ?? '-' }}</span>
                    </div>
                </div>
            </div>


            <div class="col-span-3 rounded-2xl border border-slate-200/80 bg-white/90 p-4">
                <h3 class="text-sm font-semibold text-slate-900 uppercase">{{ __('Curso') }}</h3>

                <div class="flex flex-col gap-3 text-sm text-slate-700">
                    <div>
                        <span class="text-slate-500">{{ __('Slogan') }}</span>
                        <p class="mt-1 text-slate-900">{{ $course?->slogan ?? __('Não informado') }}</p>
                    </div>
                    <div>
                        <span class="text-slate-500">{{ __('Descrição') }}</span>
                        <p class="mt-1 text-slate-900">{{ $course?->description ?? __('Não informado') }}</p>
                    </div>
                    <div>
                        <span class="text-slate-500">{{ __('Público-alvo') }}</span>
                        <p class="mt-1 text-slate-900">{{ $course?->targetAudience ?? __('Não informado') }}</p>
                    </div>
                    <div>
                        <span class="text-slate-500">{{ __('Know-how') }}</span>
                        <p class="mt-1 text-slate-900">{{ $course?->knowhow ?? __('Não informado') }}</p>
                    </div>
                    <div>
                        <span class="text-slate-500">{{ __('Link') }}</span>
                        <p class="mt-1 text-slate-900">{{ $course?->learnMoreLink ?? __('Não informado') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section
        class="rounded-2xl border border-amber-300/20 bg-linear-to-br from-slate-100 via-white to-slate-200 p-6 shadow-lg">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b-2 border-slate-200/80 pb-2">
            <div>
                <h3 class="text-lg font-semibold text-slate-900" style="font-family: 'Cinzel', serif;">
                    {{ __('Informações do evento') }}
                </h3>
                <p class="text-sm text-slate-600">{{ __('Custos, métricas e observações do treinamento.') }}</p>
            </div>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-3">
            <div class="rounded-2xl border border-slate-200/80 bg-white/90 p-4">
                <h4 class="text-sm font-semibold text-slate-900 uppercase">{{ __('Valores') }}</h4>
                <div class="mt-3 flex flex-col gap-3 text-sm text-slate-700">
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500">{{ __('Preço') }}</span>
                        <span class="font-semibold text-slate-900">{{ $training->price ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500">{{ __('Preço igreja') }}</span>
                        <span class="font-semibold text-slate-900">{{ $training->price_church ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500">{{ __('Desconto') }}</span>
                        <span class="font-semibold text-slate-900">{{ $training->discount ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500">{{ __('Total') }}</span>
                        <span class="font-semibold text-slate-900">{{ $training->payment ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white/90 p-4">
                <h4 class="text-sm font-semibold text-slate-900 uppercase">{{ __('Indicadores') }}</h4>
                <div class="mt-3 grid gap-3 text-sm text-slate-700">
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500">{{ __('Kits') }}</span>
                        <span class="font-semibold text-slate-900">{{ $training->kits ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500">{{ __('Total alunos') }}</span>
                        <span class="font-semibold text-slate-900">{{ $training->totStudents ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500">{{ __('Total igrejas') }}</span>
                        <span class="font-semibold text-slate-900">{{ $training->totChurches ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500">{{ __('Novas igrejas') }}</span>
                        <span class="font-semibold text-slate-900">{{ $training->totNewChurches ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500">{{ __('Pastores') }}</span>
                        <span class="font-semibold text-slate-900">{{ $training->totPastors ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white/90 p-4">
                <h4 class="text-sm font-semibold text-slate-900 uppercase">{{ __('Resultados') }}</h4>
                <div class="mt-3 grid gap-3 text-sm text-slate-700">
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500">{{ __('Kits recebidos') }}</span>
                        <span class="font-semibold text-slate-900">{{ $training->totKitsReceived ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500">{{ __('Kits usados') }}</span>
                        <span class="font-semibold text-slate-900">{{ $training->totKitsUsed ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500">{{ __('Abordagens') }}</span>
                        <span class="font-semibold text-slate-900">{{ $training->totApproaches ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500">{{ __('Decisões') }}</span>
                        <span class="font-semibold text-slate-900">{{ $training->totDecisions ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500">{{ __('Ouvintes') }}</span>
                        <span class="font-semibold text-slate-900">{{ $training->totListeners ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white/90 p-4 lg:col-span-2">
                <h4 class="text-sm font-semibold text-slate-900 uppercase">{{ __('Datas do evento') }}</h4>
                <div class="mt-3 flex flex-col gap-3 text-sm text-slate-700">
                    @forelse ($eventDates as $eventDate)
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <span class="font-semibold text-slate-900">
                                {{ \Carbon\Carbon::parse($eventDate->date)->format('d/m/Y') }}
                            </span>
                            <span class="text-slate-500">
                                {{ $eventDate->start_time }} - {{ $eventDate->end_time }}
                            </span>
                        </div>
                    @empty
                        <span class="text-slate-500">{{ __('Nenhuma data cadastrada.') }}</span>
                    @endforelse
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white/90 p-4">
                <h4 class="text-sm font-semibold text-slate-900 uppercase">{{ __('Participantes') }}</h4>
                <div class="mt-3 flex flex-col gap-3 text-sm text-slate-700">
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500">{{ __('Total') }}</span>
                        <span class="font-semibold text-slate-900">{{ $students->count() }}</span>
                    </div>
                    @if ($students->isNotEmpty())
                        <div class="flex flex-col gap-2 text-xs text-slate-500">
                            @foreach ($students->take(6) as $student)
                                <span>{{ $student->name }}</span>
                            @endforeach
                            @if ($students->count() > 6)
                                <span class="font-semibold text-slate-700">
                                    {{ __('+ :count participantes', ['count' => $students->count() - 6]) }}
                                </span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white/90 p-4 lg:col-span-3">
                <h4 class="text-sm font-semibold text-slate-900 uppercase">{{ __('Observações') }}</h4>
                <p class="mt-3 text-sm text-slate-700">
                    {{ $training->notes ?? __('Nenhuma observação registrada.') }}
                </p>
            </div>
        </div>
    </section>

    @if ($bannerUrl)
        <section class="rounded-2xl border border-amber-300/20 bg-white p-4 shadow-lg">
            <h3 class="text-sm font-semibold text-slate-900 uppercase">{{ __('Banner do treinamento') }}</h3>
            <div class="mt-3 overflow-hidden rounded-xl border border-slate-200/80">
                <img src="{{ $bannerUrl }}" alt="{{ __('Banner do treinamento') }}"
                    class="h-64 w-full object-cover">
            </div>
        </section>
    @endif
</div>
