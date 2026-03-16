<x-layouts.app :title="__('Relatorios recebidos')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <x-app.portal.page-header
            eyebrow="Portal Staff"
            title="Relatorios recebidos"
            description="Fila consolidada dos eventos com evidencias recebidas do campo, lacunas de envio e casos que exigem follow-up institucional."
            :breadcrumbs="[
                ['label' => 'Portais', 'url' => route('app.start')],
                ['label' => 'Staff / Governanca', 'url' => route('app.portal.staff.dashboard')],
                ['label' => 'Relatorios recebidos', 'current' => true],
            ]" />

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-app.portal.stat-card label="Na fila" :value="$reportsIndex['counts']['items']" hint="Itens exibidos com o filtro atual." />
            <x-app.portal.stat-card label="Pendentes de envio" :value="$reportsIndex['counts']['pending_submission']" hint="Eventos concluídos aguardando fontes do campo." tone="amber" />
            <x-app.portal.stat-card label="Aguardando leitura" :value="$reportsIndex['counts']['awaiting_review']" hint="Evidencias prontas para leitura cruzada do Staff." tone="sky" />
            <x-app.portal.stat-card label="Follow-up" :value="$reportsIndex['counts']['follow_up']" hint="Eventos com sinalizacao institucional ativa." tone="amber" />
        </section>

        <section class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex flex-wrap gap-3">
                @foreach ($reportsIndex['filters'] as $filter)
                    @php
                        $active = $reportsIndex['filter'] === $filter['key'];
                    @endphp

                    <a href="{{ $filter['route'] }}"
                        class="{{ $active ? 'border-sky-300 bg-sky-50 text-sky-950' : 'border-neutral-200 bg-neutral-50 text-neutral-700' }} rounded-full border px-4 py-2 text-sm font-semibold transition hover:border-sky-300 hover:bg-sky-50">
                        {{ $filter['label'] }} · {{ $filter['count'] }}
                    </a>
                @endforeach
            </div>

            <div class="grid gap-4">
                @forelse ($reportsIndex['items'] as $item)
                    @php
                        $toneClasses = match ($item['tone']) {
                            'amber' => 'border-amber-200 bg-amber-50',
                            'emerald' => 'border-emerald-200 bg-emerald-50',
                            default => 'border-sky-200 bg-sky-50',
                        };
                    @endphp

                    <article class="rounded-3xl border p-5 shadow-sm {{ $toneClasses }}">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="flex flex-col gap-3">
                                <div>
                                    <h2 class="text-lg font-semibold text-neutral-950">{{ $item['title'] }}</h2>
                                    <p class="text-sm text-neutral-600">{{ $item['church_name'] }} · {{ $item['teacher_name'] }}</p>
                                    <p class="text-xs font-medium uppercase tracking-[0.18em] text-neutral-500">{{ $item['schedule_summary'] }}</p>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    @foreach ($item['sources'] as $source)
                                        <span class="rounded-full bg-white/80 px-3 py-1 text-xs font-semibold text-neutral-700">
                                            {{ $source['label'] }}: {{ $source['status_label'] }}
                                        </span>
                                    @endforeach

                                    @if ($item['classification'])
                                        <span class="rounded-full bg-neutral-900 px-3 py-1 text-xs font-semibold text-white">
                                            {{ $item['classification'] }}
                                        </span>
                                    @endif
                                </div>

                                @if ($item['pending_sources'] !== [])
                                    <div class="rounded-2xl border border-white/70 bg-white/70 px-4 py-3 text-sm text-neutral-700">
                                        {{ __('Pendentes de envio: :sources', ['sources' => implode(', ', $item['pending_sources'])]) }}
                                    </div>
                                @endif

                                @if ($item['latest_review_comment'])
                                    <div class="rounded-2xl border border-white/70 bg-white/70 px-4 py-3 text-sm text-neutral-700">
                                        <div class="font-semibold text-neutral-900">{{ __('Ultima observacao do Staff') }}</div>
                                        <div class="mt-1">{{ $item['latest_review_comment'] }}</div>
                                    </div>
                                @endif
                            </div>

                            <div class="flex shrink-0 flex-col items-start gap-3">
                                <span class="rounded-full bg-white/80 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-neutral-800">
                                    {{ $item['status_label'] }}
                                </span>

                                <a href="{{ $item['comparison_route'] }}"
                                    class="inline-flex items-center justify-center rounded-xl border border-neutral-200 bg-white px-4 py-2 text-sm font-semibold text-neutral-900 transition hover:border-sky-300 hover:bg-sky-50">
                                    {{ __('Abrir comparacao') }}
                                </a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 p-6 text-sm text-neutral-600">
                        {{ __('Nenhum evento corresponde ao filtro atual.') }}
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-layouts.app>
