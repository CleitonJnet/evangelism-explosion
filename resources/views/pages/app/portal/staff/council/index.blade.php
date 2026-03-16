<x-layouts.app :title="__('Conselho Nacional')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <x-app.portal.page-header
            eyebrow="Portal Staff"
            title="Conselho Nacional"
            description="Landing inicial do Conselho, separada da operacao de eventos e preparada para evoluir documentos, pautas e deliberacoes."
            :breadcrumbs="[
                ['label' => 'Portais', 'url' => route('app.start')],
                ['label' => 'Staff / Governanca', 'url' => route('app.portal.staff.dashboard')],
                ['label' => 'Conselho', 'current' => true],
            ]" />

        <section class="grid gap-4 md:grid-cols-3">
            <x-app.portal.stat-card label="Trilhas iniciais" :value="$council['summary']['tracks_count']" hint="Pilares estruturantes da area do Conselho." tone="sky" />
            <x-app.portal.stat-card label="Placeholders" :value="$council['summary']['placeholders_count']" hint="Espacos reservados para a proxima fase de evolucao." />
            <x-app.portal.stat-card label="Curadoria institucional" :value="$council['summary']['can_curate'] ? __('Ativa') : __('Leitura')" hint="Board e direcao podem liderar a curadoria desta area." tone="emerald" />
        </section>

        <section class="grid gap-4 xl:grid-cols-[minmax(0,1.1fr)_minmax(20rem,0.9fr)]">
            <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-neutral-950">{{ __('Estrutura inicial do Conselho') }}</h2>
                    <p class="text-sm text-neutral-600">{{ __('A ideia aqui e criar uma base limpa para governanca colegiada, sem misturar esta camada com a fila operacional de evidencias dos eventos.') }}</p>
                </div>

                <div class="grid gap-4">
                    @foreach ($council['tracks'] as $track)
                        <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-5">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <h3 class="text-base font-semibold text-neutral-950">{{ $track['title'] }}</h3>
                                    <p class="mt-1 text-sm text-neutral-600">{{ $track['description'] }}</p>
                                </div>

                                <span class="rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-sky-800">
                                    {{ $track['status'] }}
                                </span>
                            </div>

                            <div class="mt-4 flex flex-wrap gap-2">
                                @foreach ($track['items'] as $item)
                                    <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-neutral-700">{{ $item }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex flex-col gap-4">
                <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                    <div class="mb-4">
                        <h2 class="text-lg font-semibold text-neutral-950">{{ __('Principios de desenho') }}</h2>
                        <p class="text-sm text-neutral-600">{{ __('Guardrails para o Conselho nascer bem separado das frentes de operacao e acompanhamento cotidiano.') }}</p>
                    </div>

                    <div class="grid gap-3">
                        @foreach ($council['guiding_principles'] as $principle)
                            <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4 text-sm text-neutral-700">
                                {{ $principle }}
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                    <div class="mb-4">
                        <h2 class="text-lg font-semibold text-neutral-950">{{ __('Proximos incrementos') }}</h2>
                        <p class="text-sm text-neutral-600">{{ __('Espacos naturais para evolucao sem precisar reorganizar o portal Staff depois.') }}</p>
                    </div>

                    <div class="grid gap-3">
                        @foreach ($council['next_steps'] as $step)
                            <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4 text-sm text-neutral-700">
                                {{ $step }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-layouts.app>
